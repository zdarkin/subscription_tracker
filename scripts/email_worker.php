<?php

/**
 * Email Alert Worker Script
 * -------------------------------------------------------
 * Run this script daily via Windows Task Scheduler or cron.
 *
 * Example Windows Task Scheduler action:
 *   Program: C:\laragon\bin\php\php8.x\php.exe
 *   Arguments: "C:\laragon\www\subscription-tracker\scripts\email_worker.php"
 *
 * Example Unix cron (Laragon bash/WSL):
 *   0 7 * * * /usr/bin/php /path/to/subscription-tracker/scripts/email_worker.php >> /path/to/logs/email_worker.log 2>&1
 *
 * -------------------------------------------------------
 */

$projectRoot = dirname(__DIR__);

require_once $projectRoot . '/config/env.php';
require_once $projectRoot . '/config/config.php';
require_once $projectRoot . '/models/Subscription.php';

// PHPMailer (installed via Composer)
$vendorAutoload = $projectRoot . '/vendor/autoload.php';
if (!file_exists($vendorAutoload)) {
  die("[ERROR] PHPMailer not installed. Run: composer install\n");
}
require_once $vendorAutoload;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception as MailerException;

// ------------------------------------------------------------------
// Bootstrap
// ------------------------------------------------------------------
$config       = require $projectRoot . '/config/config.php';
$mailCfg      = $config['mail'];
$alertCfg     = $config['alerts'];

// Parse ALERT_DAYS_BEFORE list (e.g. "3,1")
$daysBeforeList = array_map('intval', explode(',', $alertCfg['days_before']));
rsort($daysBeforeList); // Process further days first (e.g., 3 then 1)

$subModel     = new Subscription();
$totalSent    = 0;
$totalFailed  = 0;
$timestamp    = date('Y-m-d H:i:s');

echo "[$timestamp] Email Worker Started — Checking renewals for milestones: " . implode(', ', $daysBeforeList) . " days...\n\n";

foreach ($daysBeforeList as $daysAhead) {
  $dueItems = $subModel->getDueForAlert($daysAhead);

  if (empty($dueItems)) {
    echo "[$timestamp] [Milestone: {$daysAhead} days] Found 0 subscription(s) due for alert.\n";
    continue;
  }

  echo "[$timestamp] [Milestone: {$daysAhead} days] Found " . count($dueItems) . " subscription(s) due for alert:\n";

  foreach ($dueItems as $item) {
    $mail = new PHPMailer(true);

    try {
      // SMTP Configuration
      $mail->isSMTP();
      $mail->Host        = $mailCfg['host'];
      
      // Enable SMTP authentication only if username is provided
      $mail->SMTPAuth    = !empty($mailCfg['username']);
      if ($mail->SMTPAuth) {
        $mail->Username    = $mailCfg['username'];
        $mail->Password    = $mailCfg['password'];
      }
      
      // Determine encryption setting dynamically
      $encryption = strtolower($mailCfg['encryption'] ?? '');
      if ($encryption === 'tls') {
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
      } elseif ($encryption === 'ssl') {
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
      } else {
        $mail->SMTPSecure = '';
        $mail->SMTPAutoTLS = false; // Prevent PHPMailer from upgrading to TLS on unencrypted local ports
      }
      
      $mail->Port        = $mailCfg['port'];

      // Recipients
      $mail->setFrom($mailCfg['from_address'], $mailCfg['from_name']);
      $mail->addAddress($item['user_email'], $item['username']);

      // Dynamically calculate actual remaining days
      $today = new DateTime(date('Y-m-d'));
      $renewal = new DateTime($item['renewal_date']);
      $diff = $today->diff($renewal);
      $daysRemaining = (int) $diff->format('%r%a');

      if ($daysRemaining === 1) {
        $daysText = 'tomorrow';
        $subjectText = 'tomorrow';
      } elseif ($daysRemaining === 0) {
        $daysText = 'today';
        $subjectText = 'today';
      } else {
        $daysText = "in {$daysRemaining} days";
        $subjectText = "in {$daysRemaining} days";
      }

      // Build email body
      $serviceName  = htmlspecialchars($item['service_name'], ENT_QUOTES);
      $renewalDate  = date('l, F j, Y', strtotime($item['renewal_date']));
      $cost         = '₱' . number_format((float) $item['cost'], 2);
      $cycle        = ucfirst($item['billing_cycle']);
      $userName     = htmlspecialchars($item['username'], ENT_QUOTES);
      $appName      = $config['app']['name'];
      $appUrl       = $config['app']['url'];

      $mail->isHTML(true);
      $mail->Subject = "Renewal Reminder: {$serviceName} renews " . ($daysRemaining === 0 ? "today" : $subjectText);
      $mail->Body    = buildEmailHtml($userName, $serviceName, $renewalDate, $cost, $cycle, $daysText, $appName, $appUrl);
      $mail->AltBody = buildEmailText($userName, $serviceName, $renewalDate, $cost, $cycle, $daysText, $appName);

      $mail->send();

      // Log successful send to prevent duplicate alerts for this milestone
      $subModel->logAlert((int) $item['id'], (int) $item['user_id'], $item['renewal_date'], $daysAhead);

      echo " Sent to {$item['user_email']} for [{$item['service_name']}] (ID: {$item['id']})\n";
      $totalSent++;
    } catch (MailerException $e) {
      echo " Failed for {$item['user_email']} [{$item['service_name']}]: " . $mail->ErrorInfo . "\n";
      error_log("[EmailWorker] Failed: " . $mail->ErrorInfo);
      $totalFailed++;
    }
  }
  echo "\n";
}

$finish = date('Y-m-d H:i:s');
echo "[$finish] Worker Finished. Sent: {$totalSent} | Failed: {$totalFailed}\n";
exit($totalFailed > 0 ? 1 : 0);


// ------------------------------------------------------------------
// HTML Email Template
// ------------------------------------------------------------------
function buildEmailHtml(
  string $userName,
  string $serviceName,
  string $renewalDate,
  string $cost,
  string $cycle,
  string $daysText,
  string $appName,
  string $appUrl
): string {
  return <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"/>
<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
<title>Subscription Renewal Reminder</title>
</head>
<body style="margin:0;padding:0;background:#0a0a0f;font-family:Inter,Arial,sans-serif;">
<table width="100%" cellpadding="0" cellspacing="0" style="background:#0a0a0f;padding:40px 20px;">
  <tr><td align="center">
    <table width="600" cellpadding="0" cellspacing="0" style="background:#1a1a27;border-radius:16px;border:1px solid rgba(255,255,255,0.08);overflow:hidden;max-width:600px;">

      <!-- Header -->
      <tr>
        <td style="background:linear-gradient(135deg,#6366f1,#8b5cf6);padding:32px 40px;text-align:center;">
          <p style="margin:0;font-size:28px;font-weight:800;color:#ffffff;letter-spacing:-0.5px;">{$appName}</p>
          <p style="margin:8px 0 0;font-size:14px;color:rgba(255,255,255,0.8);">Renewal Reminder</p>
        </td>
      </tr>

      <!-- Body -->
      <tr>
        <td style="padding:36px 40px;">
          <p style="margin:0 0 6px;font-size:18px;font-weight:600;color:#ffffff;">Hi {$userName},</p>
          <p style="margin:0 0 28px;font-size:14px;color:#9ca3af;line-height:1.6;">
            Your subscription to <strong style="color:#ffffff;">{$serviceName}</strong>
            is renewing <strong style="color:#6366f1;">{$daysText}</strong>. Here are the details:
          </p>

          <!-- Subscription Detail Box -->
          <table width="100%" cellpadding="0" cellspacing="0" style="background:#22223b;border-radius:12px;border:1px solid rgba(255,255,255,0.06);margin-bottom:28px;">
            <tr>
              <td style="padding:20px 24px;">
                <table width="100%" cellpadding="0" cellspacing="8">
                  <tr>
                    <td style="font-size:13px;color:#6b7280;padding:6px 0;">Service</td>
                    <td style="font-size:13px;color:#ffffff;font-weight:600;text-align:right;padding:6px 0;">{$serviceName}</td>
                  </tr>
                  <tr>
                    <td style="font-size:13px;color:#6b7280;padding:6px 0;border-top:1px solid rgba(255,255,255,0.05);">Cost</td>
                    <td style="font-size:13px;color:#ffffff;font-weight:600;text-align:right;padding:6px 0;border-top:1px solid rgba(255,255,255,0.05);">{$cost}</td>
                  </tr>
                  <tr>
                    <td style="font-size:13px;color:#6b7280;padding:6px 0;border-top:1px solid rgba(255,255,255,0.05);">Billing Cycle</td>
                    <td style="font-size:13px;color:#ffffff;font-weight:600;text-align:right;padding:6px 0;border-top:1px solid rgba(255,255,255,0.05);">{$cycle}</td>
                  </tr>
                  <tr>
                    <td style="font-size:13px;color:#6b7280;padding:6px 0;border-top:1px solid rgba(255,255,255,0.05);">Renewal Date</td>
                    <td style="font-size:13px;color:#6366f1;font-weight:700;text-align:right;padding:6px 0;border-top:1px solid rgba(255,255,255,0.05);">{$renewalDate}</td>
                  </tr>
                </table>
              </td>
            </tr>
          </table>

          <!-- CTA -->
          <div style="text-align:center;margin-bottom:28px;">
            <a href="{$appUrl}/subscriptions"
               style="display:inline-block;padding:14px 32px;background:linear-gradient(135deg,#6366f1,#8b5cf6);color:#ffffff;font-size:14px;font-weight:600;border-radius:10px;text-decoration:none;letter-spacing:0.3px;">
              View My Subscriptions →
            </a>
          </div>

          <p style="margin:0;font-size:13px;color:#4b5563;line-height:1.6;text-align:center;">
            You received this reminder because you have an active account on {$appName}.<br/>
            Log in to manage or update your subscriptions anytime.
          </p>
        </td>
      </tr>

      <!-- Footer -->
      <tr>
        <td style="padding:20px 40px;border-top:1px solid rgba(255,255,255,0.05);text-align:center;">
          <p style="margin:0;font-size:12px;color:#374151;">&copy; {$appName} · All rights reserved</p>
        </td>
      </tr>

    </table>
  </td></tr>
</table>
</body>
</html>
HTML;
}

// ------------------------------------------------------------------
// Plain Text Email Fallback
// ------------------------------------------------------------------
function buildEmailText(
  string $userName,
  string $serviceName,
  string $renewalDate,
  string $cost,
  string $cycle,
  string $daysText,
  string $appName
): string {
  return <<<TEXT
Hi {$userName},

Your subscription to "{$serviceName}" is renewing {$daysText}.

Details:
  Service:       {$serviceName}
  Cost:          {$cost}
  Billing Cycle: {$cycle}
  Renewal Date:  {$renewalDate}

Log in to your {$appName} account to review or update this subscription.

---
{$appName} — Subscription Tracker
TEXT;
}

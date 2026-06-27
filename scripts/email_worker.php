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

// Enable error reporting to diagnose Web/CGI issues
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Web Execution Token Security
$isCli = (php_sapi_name() === 'cli');
if (!$isCli) {
  header('Content-Type: text/plain; charset=utf-8');
  $urlToken    = $_GET['token'] ?? '';
  $secureToken = $_ENV['WORKER_TOKEN'] ?? '';
  
  if (empty($secureToken) || $urlToken !== $secureToken) {
    http_response_code(403);
    die("Forbidden: Invalid or missing token.\n");
  }
}

// Mark this cron run as completed for today immediately to avoid double execution
file_put_contents($projectRoot . '/cron_last_run.txt', date('Y-m-d'));

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

    $serviceName  = htmlspecialchars($item['service_name'], ENT_QUOTES);
    $renewalDate  = date('l, F j, Y', strtotime($item['renewal_date']));
    $cost         = '₱' . number_format((float) $item['cost'], 2);
    $cycle        = ucfirst($item['billing_cycle']);
    $userName     = htmlspecialchars($item['username'], ENT_QUOTES);
    $appName      = $config['app']['name'];
    $appUrl       = $config['app']['url'];

    $subject      = "Renewal Reminder: {$serviceName} renews " . ($daysRemaining === 0 ? "today" : $subjectText);
    $htmlBody     = buildEmailHtml($userName, $serviceName, $renewalDate, $cost, $cycle, $daysText, $appName, $appUrl);
    $textBody     = buildEmailText($userName, $serviceName, $renewalDate, $cost, $cycle, $daysText, $appName);

    if ($mailCfg['mailer'] === 'resend') {
      // Send via Resend HTTP API
      try {
        $apiKey = $mailCfg['resend_key'];
        if (empty($apiKey)) {
          throw new Exception("Missing RESEND_API_KEY in environment configuration.");
        }

        $url = 'https://api.resend.com/emails';
        $postData = [
          'from'    => "{$mailCfg['from_name']} <{$mailCfg['from_address']}>",
          'to'      => [$item['user_email']],
          'subject' => $subject,
          'html'    => $htmlBody,
          'text'    => $textBody
        ];

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($postData));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
          'Authorization: Bearer ' . $apiKey,
          'Content-Type: application/json'
        ]);

        $response  = curl_exec($ch);
        $httpCode  = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        if ($curlError) {
          throw new Exception("CURL Error: " . $curlError);
        }

        if ($httpCode >= 400) {
          throw new Exception("Resend API returned status code {$httpCode}: " . $response);
        }

        // Log successful send
        $subModel->logAlert((int) $item['id'], (int) $item['user_id'], $item['renewal_date'], $daysAhead);

        echo " Sent to {$item['user_email']} via Resend for [{$item['service_name']}] (ID: {$item['id']})\n";
        $totalSent++;
      } catch (Exception $e) {
        echo " Failed for {$item['user_email']} via Resend [{$item['service_name']}]: " . $e->getMessage() . "\n";
        error_log("[EmailWorker] Resend Failed: " . $e->getMessage());
        $totalFailed++;
      }
    } else {
      // Send via PHPMailer SMTP
      $mail = new PHPMailer(true);

      try {
        $mail->isSMTP();
        $mail->Host        = $mailCfg['host'];
        
        $mail->SMTPAuth    = !empty($mailCfg['username']);
        if ($mail->SMTPAuth) {
          $mail->Username    = $mailCfg['username'];
          $mail->Password    = $mailCfg['password'];
        }
        
        $encryption = strtolower($mailCfg['encryption'] ?? '');
        if ($encryption === 'tls') {
          $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        } elseif ($encryption === 'ssl') {
          $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        } else {
          $mail->SMTPSecure = '';
          $mail->SMTPAutoTLS = false;
        }
        
        $mail->Port        = $mailCfg['port'];

        // Recipients
        $mail->setFrom($mailCfg['from_address'], $mailCfg['from_name']);
        $mail->addAddress($item['user_email'], $item['username']);

        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body    = $htmlBody;
        $mail->AltBody = $textBody;

        $mail->send();

        // Log successful send
        $subModel->logAlert((int) $item['id'], (int) $item['user_id'], $item['renewal_date'], $daysAhead);

        echo " Sent to {$item['user_email']} via SMTP for [{$item['service_name']}] (ID: {$item['id']})\n";
        $totalSent++;
      } catch (MailerException $e) {
        echo " Failed for {$item['user_email']} via SMTP [{$item['service_name']}]: " . $mail->ErrorInfo . "\n";
        error_log("[EmailWorker] SMTP Failed: " . $mail->ErrorInfo);
        $totalFailed++;
      }
    }
  }
  echo "\n";
}

$finish = date('Y-m-d H:i:s');
echo "[$finish] Worker Finished. Sent: {$totalSent} | Failed: {$totalFailed}\n";
if ($isCli) {
    exit($totalFailed > 0 ? 1 : 0);
}
exit;


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

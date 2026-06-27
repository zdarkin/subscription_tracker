<?php

/**
 * Application Configuration
 * Reads settings from the parsed .env values.
 */

require_once __DIR__ . '/env.php';

return [
    'app' => [
        'name'    => $_ENV['APP_NAME']  ?? 'Subscription Tracker',
        'url'     => $_ENV['APP_URL']   ?? 'http://localhost/subscription-tracker/public',
        'env'     => $_ENV['APP_ENV']   ?? 'production',
        'debug'   => ($_ENV['APP_DEBUG'] ?? 'false') === 'true',
    ],
    'session' => [
        'timeout' => (int) ($_ENV['SESSION_TIMEOUT'] ?? 1800),
        'name'    => $_ENV['SESSION_NAME'] ?? 'substrkr_sess',
    ],
    'mail' => [
        'mailer'       => $_ENV['MAIL_MAILER']       ?? 'smtp',
        'resend_key'   => $_ENV['RESEND_API_KEY']    ?? '',
        'host'         => $_ENV['MAIL_HOST']         ?? 'smtp.gmail.com',
        'port'         => (int) ($_ENV['MAIL_PORT']  ?? 587),
        'username'     => $_ENV['MAIL_USERNAME']     ?? '',
        'password'     => $_ENV['MAIL_PASSWORD']     ?? '',
        'from_address' => $_ENV['MAIL_FROM_ADDRESS']  ?? $_ENV['MAIL_USERNAME'] ?? '',
        'from_name'    => $_ENV['MAIL_FROM_NAME']    ?? 'Subscription Tracker',
        'encryption'   => $_ENV['MAIL_ENCRYPTION']   ?? 'tls',
    ],
    'alerts' => [
        'days_before' => $_ENV['ALERT_DAYS_BEFORE'] ?? '3',
    ],
];

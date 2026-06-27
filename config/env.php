<?php

/**
 * .env Parser
 * Lightweight parser that reads key=value pairs from the .env file
 * and populates $_ENV and putenv().
 */
class Env
{
    public static function load(string $path): void
    {
        if (!file_exists($path)) {
            return;
        }

        $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

        foreach ($lines as $line) {
            $line = trim($line);

            // Skip comments and empty lines
            if (str_starts_with($line, '#') || $line === '') {
                continue;
            }

            // Only process lines that contain '='
            if (!str_contains($line, '=')) {
                continue;
            }

            [$key, $value] = explode('=', $line, 2);
            $key   = trim($key);
            $value = trim($value);

            // Strip surrounding quotes and inline comments robustly
            if (preg_match('/^"([^"]*)"/', $value, $matches)) {
                $value = $matches[1];
            } elseif (preg_match('/^\'([^\']*)\'/', $value, $matches)) {
                $value = $matches[1];
            } else {
                // For unquoted values, strip any inline comments
                if (str_contains($value, '#')) {
                    [$value] = explode('#', $value, 2);
                    $value = trim($value);
                }
            }

            if (!array_key_exists($key, $_ENV)) {
                putenv("{$key}={$value}");
                $_ENV[$key]    = $value;
                $_SERVER[$key] = $value;
            }
        }
    }
}

// Load environment variables from project root
Env::load(dirname(__DIR__) . '/.env');

// Set default application timezone
date_default_timezone_set($_ENV['APP_TIMEZONE'] ?? 'Asia/Manila');

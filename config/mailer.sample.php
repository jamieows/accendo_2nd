<?php
// config/mailer.sample.php
// Sample mailer helper for password reset. Copy to config/mailer.php and
// configure a real SMTP provider (PHPMailer recommended) for production.

// DEV MODE: if you don't configure SMTP, this stub will log the reset emails
// to a local file for testing on localhost/phpMyAdmin.

function send_mail($to, $subject, $body, $html = false) {
    // Try to use a real mailer if available
    if (file_exists(__DIR__ . '/mailer.php')) {
        // If user created a real config, include and use its send_mail
        require_once __DIR__ . '/mailer.php';
        if (function_exists('send_mail')) {
            return send_mail($to, $subject, $body, $html);
        }
    }

    // Fallback: append to a local log (safe for local testing)
    $log = __DIR__ . '/../password_reset_emails.log';
    $entry = "---\nTo: $to\nSubject: $subject\nTime: " . date('c') . "\n\n" . $body . "\n";
    file_put_contents($log, $entry, FILE_APPEND | LOCK_EX);
    return true;
}

/*
Notes:
- To enable real email sending, install PHPMailer (composer require phpmailer/phpmailer)
  and create `config/mailer.php` with a send_mail() implementation that uses SMTP.
- Example: use SMTP with TLS, set Host, Username, Password, Port.
*/

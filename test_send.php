<?php
require_once __DIR__ . '/includes/Mailer.php';

$mailer = new AppMailer();

$sent = $mailer->send(
    'trxmcln@gmail.com',
    'Test User',
    'Air-TRX Test Email',
    '<h2>Hello!</h2><p>This is a test email from Air-TRX using Gmail SMTP.</p>',
    'Hello! This is a test email from Air-TRX using Gmail SMTP.'
);

if ($sent) {
    echo 'SENT';
} else {
    echo 'FAILED<br>';
    echo 'Error: ' . htmlspecialchars($mailer->getLastError());
}
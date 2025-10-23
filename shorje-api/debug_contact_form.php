<?php

require_once 'vendor/autoload.php';

use Symfony\Component\Dotenv\Dotenv;
use App\Service\EmailService;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mailer\Transport;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;

// Load environment variables
$dotenv = new Dotenv();
$dotenv->load(__DIR__.'/.env');

echo "=== DEBUGGING CONTACT FORM EMAIL ===\n\n";

// Test 1: Check environment variables
echo "1. Environment Variables:\n";
echo "MAILER_DSN: " . $_ENV['MAILER_DSN'] . "\n";
echo "MAILER_FROM_EMAIL: " . $_ENV['MAILER_FROM_EMAIL'] . "\n";
echo "MAILER_FROM_NAME: " . $_ENV['MAILER_FROM_NAME'] . "\n\n";

// Test 2: Create mailer
echo "2. Creating Mailer...\n";
try {
    $transport = Transport::fromDsn($_ENV['MAILER_DSN']);
    $mailer = new \Symfony\Component\Mailer\Mailer($transport);
    echo "✅ Mailer created successfully\n\n";
} catch (Exception $e) {
    echo "❌ Failed to create mailer: " . $e->getMessage() . "\n\n";
    exit(1);
}

// Test 3: Create Twig environment
echo "3. Creating Twig Environment...\n";
try {
    $loader = new FilesystemLoader(__DIR__.'/templates');
    $twig = new Environment($loader);
    echo "✅ Twig environment created successfully\n\n";
} catch (Exception $e) {
    echo "❌ Failed to create Twig: " . $e->getMessage() . "\n\n";
    exit(1);
}

// Test 4: Create EmailService
echo "4. Creating EmailService...\n";
try {
    $emailService = new EmailService(
        $mailer,
        $twig,
        $_ENV['MAILER_FROM_EMAIL'],
        $_ENV['MAILER_FROM_NAME']
    );
    echo "✅ EmailService created successfully\n\n";
} catch (Exception $e) {
    echo "❌ Failed to create EmailService: " . $e->getMessage() . "\n\n";
    exit(1);
}

// Test 5: Send contact form email
echo "5. Sending Contact Form Email...\n";
try {
    $emailService->sendContactFormEmail(
        'Debug Test User',
        'debug@example.com',
        'Debug Contact Form Test',
        'This is a debug test message to verify contact form email functionality.'
    );
    echo "✅ Contact form email sent successfully!\n";
    echo "Check inbox at: " . $_ENV['MAILER_FROM_EMAIL'] . "\n\n";
} catch (Exception $e) {
    echo "❌ Failed to send contact form email:\n";
    echo "Error: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . "\n";
    echo "Line: " . $e->getLine() . "\n\n";
}

// Test 6: Check if email template exists
echo "6. Checking Email Template...\n";
$templatePath = __DIR__.'/templates/emails/contact_form.html.twig';
if (file_exists($templatePath)) {
    echo "✅ Email template exists: " . $templatePath . "\n";
    echo "Template size: " . filesize($templatePath) . " bytes\n\n";
} else {
    echo "❌ Email template not found: " . $templatePath . "\n\n";
}

echo "=== DEBUG COMPLETE ===\n";

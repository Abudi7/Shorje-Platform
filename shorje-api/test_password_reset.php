<?php

require_once 'vendor/autoload.php';

use Symfony\Component\Dotenv\Dotenv;
use App\Service\EmailService;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mailer\Transport;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;
use App\Entity\User;

// Load environment variables
$dotenv = new Dotenv();
$dotenv->load(__DIR__.'/.env');

echo "=== TESTING PASSWORD RESET EMAIL ===\n\n";

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

// Test 5: Create a mock user for testing
echo "5. Creating Mock User...\n";
try {
    $user = new User();
    $user->setEmail('shorje@abdulrhman-alshalal.com');
    $user->setFirstName('Test');
    $user->setLastName('User');
    echo "✅ Mock user created successfully\n\n";
} catch (Exception $e) {
    echo "❌ Failed to create mock user: " . $e->getMessage() . "\n\n";
    exit(1);
}

// Test 6: Send password reset email
echo "6. Sending Password Reset Email...\n";
try {
    $resetToken = 'test-reset-token-12345';
    $emailService->sendPasswordResetEmail($user, $resetToken);
    echo "✅ Password reset email sent successfully!\n";
    echo "Check inbox at: " . $user->getEmail() . "\n\n";
} catch (Exception $e) {
    echo "❌ Failed to send password reset email:\n";
    echo "Error: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . "\n";
    echo "Line: " . $e->getLine() . "\n\n";
}

// Test 7: Check if password reset template exists
echo "7. Checking Password Reset Template...\n";
$templatePath = __DIR__.'/templates/emails/password_reset.html.twig';
if (file_exists($templatePath)) {
    echo "✅ Password reset template exists: " . $templatePath . "\n";
    echo "Template size: " . filesize($templatePath) . " bytes\n\n";
} else {
    echo "❌ Password reset template not found: " . $templatePath . "\n\n";
}

echo "=== PASSWORD RESET EMAIL TEST COMPLETE ===\n";

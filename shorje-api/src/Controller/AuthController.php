<?php

namespace App\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use App\Service\EmailService;
use Symfony\Component\Security\Core\Exception\UserNotFoundException;
use Symfony\Component\Uid\Uuid;

class AuthController extends AbstractController
{

    #[Route('/api/register', name: 'api_register', methods: ['POST'])]
    public function register(
        Request $request,
        EntityManagerInterface $em,
        UserPasswordHasherInterface $passwordHasher,
        MailerInterface $mailer
    ): JsonResponse {
        $data = json_decode($request->getContent(), true);
        $email = $data['email'] ?? null;
        $password = $data['password'] ?? null;

        if (!$email || !$password) {
            return new JsonResponse(['error' => 'Email and password are required'], 400);
        }

        // Check if user already exists
        $existingUser = $em->getRepository(User::class)->findOneBy(['email' => $email]);
        if ($existingUser) {
            return new JsonResponse(['error' => 'User with this email already exists'], 400);
        }

        $user = new User();
        $user->setEmail($email);
        $hashedPassword = $passwordHasher->hashPassword($user, $password);
        $user->setPassword($hashedPassword);
        
        // Generate email verification token
        $user->setEmailVerificationToken(Uuid::v4()->toRfc4122());
        $user->setIsVerified(false);
        
        // Set default avatar for new users
        $this->setDefaultAvatar($user);

        $em->persist($user);
        $em->flush();

        // Send welcome email
        try {
            $this->sendWelcomeEmail($mailer, $user);
        } catch (\Exception $e) {
            // Log error but don't fail registration
            error_log('Failed to send welcome email: ' . $e->getMessage());
        }

        return new JsonResponse([
            'message' => 'User registered successfully. Please check your email for verification.',
            'user_id' => $user->getId()
        ], 201);
    }

    #[Route('/api/forgot-password', name: 'api_forgot_password', methods: ['POST'])]
    public function forgotPassword(
        Request $request,
        EntityManagerInterface $em,
        EmailService $emailService
    ): JsonResponse {
        $data = json_decode($request->getContent(), true);
        $email = $data['email'] ?? null;

        if (!$email) {
            return new JsonResponse(['error' => 'Email is required'], 400);
        }

        $user = $em->getRepository(User::class)->findOneBy(['email' => $email]);
        
        // Always return success to prevent email enumeration
        if (!$user) {
            return new JsonResponse(['message' => 'If the email exists, a reset link has been sent.'], 200);
        }

        // Generate reset token
        $resetToken = Uuid::v4()->toRfc4122();
        $user->setResetToken($resetToken);
        $user->setResetTokenExpiresAt(new \DateTime('+1 hour'));

        $em->flush();

        // Send reset email
        try {
            $emailService->sendPasswordResetEmail($user, $resetToken);
        } catch (\Exception $e) {
            error_log('Failed to send password reset email: ' . $e->getMessage());
            return new JsonResponse(['error' => 'Failed to send reset email'], 500);
        }

        return new JsonResponse(['message' => 'If the email exists, a reset link has been sent.'], 200);
    }

    #[Route('/api/reset-password', name: 'api_reset_password', methods: ['POST'])]
    public function resetPassword(
        Request $request,
        EntityManagerInterface $em,
        UserPasswordHasherInterface $passwordHasher
    ): JsonResponse {
        $data = json_decode($request->getContent(), true);
        $token = $data['token'] ?? null;
        $password = $data['password'] ?? null;

        if (!$token || !$password) {
            return new JsonResponse(['error' => 'Token and password are required'], 400);
        }

        $user = $em->getRepository(User::class)->findOneBy(['resetToken' => $token]);
        
        if (!$user || !$user->getResetTokenExpiresAt() || $user->getResetTokenExpiresAt() < new \DateTime()) {
            return new JsonResponse(['error' => 'Invalid or expired reset token'], 400);
        }

        // Update password
        $hashedPassword = $passwordHasher->hashPassword($user, $password);
        $user->setPassword($hashedPassword);
        $user->setResetToken(null);
        $user->setResetTokenExpiresAt(null);

        $em->flush();

        return new JsonResponse(['message' => 'Password reset successfully'], 200);
    }

    #[Route('/api/verify-email/{token}', name: 'api_verify_email', methods: ['GET'])]
    public function verifyEmail(
        string $token,
        EntityManagerInterface $em
    ): JsonResponse {
        $user = $em->getRepository(User::class)->findOneBy(['emailVerificationToken' => $token]);
        
        if (!$user) {
            return new JsonResponse(['error' => 'Invalid verification token'], 400);
        }

        $user->setIsVerified(true);
        $user->setEmailVerificationToken(null);

        $em->flush();

        return new JsonResponse(['message' => 'Email verified successfully'], 200);
    }

    #[Route('/api/test', name: 'api_test', methods: ['GET'])]
    public function test(): JsonResponse
    {
        $user = $this->getUser();
        return new JsonResponse([
            'message' => 'JWT authentication working!',
            'user' => $user?->getUserIdentifier(),
            'is_verified' => $user?->isVerified()
        ]);
    }

    #[Route('/api/refresh', name: 'api_refresh', methods: ['POST'])]
    public function refresh(): JsonResponse
    {
        // This would typically generate a new token
        // For now, we'll just return success
        return new JsonResponse(['message' => 'Token refreshed successfully']);
    }

    private function sendWelcomeEmail(MailerInterface $mailer, User $user): void
    {
        $email = (new Email())
            ->from($_ENV['MAILER_FROM_EMAIL'] ?? 'noreply@shorje.com')
            ->to($user->getEmail())
            ->subject('Welcome to Shorje!')
            ->html($this->renderView('emails/welcome.html.twig', [
                'user' => $user,
                'verificationUrl' => $this->generateUrl('api_verify_email', ['token' => $user->getEmailVerificationToken()], true)
            ]));

        $mailer->send($email);
    }

    private function sendPasswordResetEmail(MailerInterface $mailer, User $user, string $token): void
    {
        $resetUrl = $this->generateUrl('app_reset_password', ['token' => $token], true);
        
        $email = (new Email())
            ->from($_ENV['MAILER_FROM_EMAIL'] ?? 'noreply@shorje.com')
            ->to($user->getEmail())
            ->subject('Password Reset Request')
            ->html($this->renderView('emails/password_reset.html.twig', [
                'user' => $user,
                'resetUrl' => $resetUrl
            ]));

        $mailer->send($email);
    }

    private function setDefaultAvatar(User $user): void
    {
        $defaultAvatarPath = __DIR__ . '/../../public/images/default-avatar.png';
        
        // Create directory if it doesn't exist
        $dir = dirname($defaultAvatarPath);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        // Create default avatar if it doesn't exist
        if (!file_exists($defaultAvatarPath)) {
            $image = imagecreate(64, 64);
            $bgColor = imagecolorallocate($image, 200, 200, 200);
            $textColor = imagecolorallocate($image, 100, 100, 100);
            
            // Draw a simple user icon
            imagefilledellipse($image, 32, 24, 32, 32, $textColor);
            imagefilledellipse($image, 32, 52, 24, 16, $textColor);
            
            imagepng($image, $defaultAvatarPath);
            imagedestroy($image);
        }

        // Read the default avatar and set it as the user's profile picture
        $avatarData = file_get_contents($defaultAvatarPath);
        $user->setProfilePicture($avatarData);
        $user->setProfilePictureMimeType('image/png');
    }
}
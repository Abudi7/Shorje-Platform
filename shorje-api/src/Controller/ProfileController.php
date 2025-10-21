<?php

namespace App\Controller;

use App\Entity\Product;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Uid\Uuid;

class ProfileController extends AbstractController
{
    #[Route('/profile', name: 'app_profile')]
    public function profile(EntityManagerInterface $em): Response
    {
        $user = $this->getUser();
        if (!$user) {
            throw new AccessDeniedException('يجب تسجيل الدخول أولاً');
        }

        // Get user's products
        $products = $em->getRepository(Product::class)->findBy(
            ['seller' => $user],
            ['createdAt' => 'DESC']
        );

        return $this->render('profile/index.html.twig', [
            'user' => $user,
            'isOwnProfile' => true,
            'products' => $products
        ]);
    }

    #[Route('/profile/{userId}', name: 'app_user_profile')]
    public function userProfile(int $userId, EntityManagerInterface $em): Response
    {
        $currentUser = $this->getUser();
        if (!$currentUser) {
            throw new AccessDeniedException('يجب تسجيل الدخول أولاً');
        }

        $user = $em->getRepository(User::class)->find($userId);
        if (!$user) {
            throw $this->createNotFoundException('المستخدم غير موجود');
        }

        // Get user's products
        $products = $em->getRepository(Product::class)->findBy(
            ['seller' => $user],
            ['createdAt' => 'DESC']
        );

        return $this->render('profile/index.html.twig', [
            'user' => $user,
            'isOwnProfile' => $currentUser->getId() === $userId,
            'products' => $products
        ]);
    }

    #[Route('/edit-profile', name: 'app_edit_profile')]
    public function editProfile(): Response
    {
        $user = $this->getUser();
        if (!$user) {
            throw new AccessDeniedException('يجب تسجيل الدخول أولاً');
        }

        return $this->render('profile/edit.html.twig', [
            'user' => $user
        ]);
    }

    #[Route('/web/profile/update', name: 'web_profile_update', methods: ['POST'])]
    public function updateProfile(
        Request $request,
        EntityManagerInterface $em,
        MailerInterface $mailer
    ): JsonResponse {
        $user = $this->getUser();
        if (!$user) {
            return new JsonResponse(['error' => 'يجب تسجيل الدخول أولاً'], 401);
        }

        $data = json_decode($request->getContent(), true);
        
        $oldEmail = $user->getEmail();
        $emailChanged = false;

        // Update profile fields
        if (isset($data['firstName'])) {
            $user->setFirstName($data['firstName']);
        }
        
        if (isset($data['lastName'])) {
            $user->setLastName($data['lastName']);
        }
        
        if (isset($data['age'])) {
            $user->setAge((int)$data['age']);
        }
        
        if (isset($data['phoneNumber'])) {
            $user->setPhoneNumber($data['phoneNumber']);
        }
        
        if (isset($data['bio'])) {
            $user->setBio($data['bio']);
        }
        
        if (isset($data['location'])) {
            $user->setLocation($data['location']);
        }
        
        if (isset($data['gender'])) {
            $user->setGender($data['gender']);
        }
        
        if (isset($data['email']) && $data['email'] !== $oldEmail) {
            $user->setEmail($data['email']);
            $user->setIsVerified(false);
            $user->setEmailVerificationToken(Uuid::v4()->toRfc4122());
            $emailChanged = true;
        }

        $em->flush();

        // Send email verification if email changed
        if ($emailChanged) {
            try {
                $this->sendEmailVerification($mailer, $user);
            } catch (\Exception $e) {
                error_log('Failed to send email verification: ' . $e->getMessage());
            }
        }

        return new JsonResponse([
            'message' => 'تم تحديث الملف الشخصي بنجاح',
            'emailChanged' => $emailChanged
        ]);
    }

    #[Route('/web/profile/upload-image', name: 'web_profile_upload_image', methods: ['POST'])]
    public function uploadImage(
        Request $request,
        EntityManagerInterface $em
    ): JsonResponse {
        $user = $this->getUser();
        if (!$user) {
            return new JsonResponse(['error' => 'يجب تسجيل الدخول أولاً'], 401);
        }

        $data = json_decode($request->getContent(), true);
        $imageType = $data['type'] ?? 'profile'; // 'profile' or 'cover'
        $imageData = $data['image'] ?? null;

        if (!$imageData) {
            return new JsonResponse(['error' => 'لم يتم إرسال صورة'], 400);
        }

        try {
            // Decode base64 image data
            if (strpos($imageData, 'data:') === 0) {
                $imageData = substr($imageData, strpos($imageData, ',') + 1);
            }
            $imageBinary = base64_decode($imageData);
            
            if ($imageBinary === false) {
                return new JsonResponse(['error' => 'صيغة الصورة غير صحيحة'], 400);
            }

            // Detect MIME type
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mimeType = finfo_buffer($finfo, $imageBinary);
            finfo_close($finfo);

            // Validate image type
            $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
            if (!in_array($mimeType, $allowedTypes)) {
                return new JsonResponse(['error' => 'نوع الصورة غير مدعوم. يرجى استخدام JPG, PNG, GIF أو WebP'], 400);
            }

            // Store image as BLOB
            if ($imageType === 'cover') {
                $user->setCoverImage($imageBinary);
                $user->setCoverImageMimeType($mimeType);
            } else {
                $user->setProfilePicture($imageBinary);
                $user->setProfilePictureMimeType($mimeType);
            }

            $em->flush();

            return new JsonResponse([
                'message' => 'تم رفع الصورة بنجاح',
                'type' => $imageType,
                'mimeType' => $mimeType
            ]);

        } catch (\Exception $e) {
            return new JsonResponse(['error' => 'حدث خطأ أثناء رفع الصورة: ' . $e->getMessage()], 500);
        }
    }

    #[Route('/web/profile/change-password', name: 'web_profile_change_password', methods: ['POST'])]
    public function changePassword(
        Request $request,
        EntityManagerInterface $em,
        UserPasswordHasherInterface $passwordHasher
    ): JsonResponse {
        $user = $this->getUser();
        if (!$user) {
            return new JsonResponse(['error' => 'يجب تسجيل الدخول أولاً'], 401);
        }

        $data = json_decode($request->getContent(), true);
        $currentPassword = $data['currentPassword'] ?? null;
        $newPassword = $data['newPassword'] ?? null;

        if (!$currentPassword || !$newPassword) {
            return new JsonResponse(['error' => 'كلمة المرور الحالية والجديدة مطلوبة'], 400);
        }

        // Verify current password
        if (!$passwordHasher->isPasswordValid($user, $currentPassword)) {
            return new JsonResponse(['error' => 'كلمة المرور الحالية غير صحيحة'], 400);
        }

        // Update password
        $hashedPassword = $passwordHasher->hashPassword($user, $newPassword);
        $user->setPassword($hashedPassword);

        $em->flush();

        return new JsonResponse(['message' => 'تم تغيير كلمة المرور بنجاح']);
    }

    private function sendEmailVerification(MailerInterface $mailer, User $user): void
    {
        $email = (new Email())
            ->from($_ENV['MAILER_FROM_EMAIL'] ?? 'noreply@shorje.com')
            ->to($user->getEmail())
            ->subject('تأكيد البريد الإلكتروني الجديد - شورجي')
            ->html($this->renderView('emails/email_verification.html.twig', [
                'user' => $user,
                'verificationUrl' => $this->generateUrl('api_verify_email', ['token' => $user->getEmailVerificationToken()], true)
            ]));

        $mailer->send($email);
    }

    #[Route('/web/profile/image/{userId}/{type}', name: 'web_profile_image', methods: ['GET'])]
    public function getProfileImage(
        int $userId,
        string $type,
        EntityManagerInterface $em
    ): Response {
        $user = $em->getRepository(User::class)->find($userId);
        
        if (!$user) {
            return new Response('User not found', 404);
        }

        $imageData = null;
        $mimeType = null;

        if ($type === 'profile') {
            $imageData = $user->getProfilePicture();
            $mimeType = $user->getProfilePictureMimeType();
        } elseif ($type === 'cover') {
            $imageData = $user->getCoverImage();
            $mimeType = $user->getCoverImageMimeType();
        }

        if (!$imageData) {
            // Return a default image or 404
            return new Response('Image not found', 404);
        }

        // Convert BLOB to string if needed
        if (is_resource($imageData)) {
            $imageData = stream_get_contents($imageData);
        }

        $response = new Response($imageData);
        $response->headers->set('Content-Type', $mimeType ?: 'image/jpeg');
        $response->headers->set('Cache-Control', 'public, max-age=3600');

        return $response;
    }
}

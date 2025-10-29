<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

class ContactController extends AbstractController
{
    #[Route('/contact', name: 'app_contact')]
    public function index(): Response
    {
        return $this->render('contact/index.html.twig');
    }

    #[Route('/api/contact', name: 'api_contact', methods: ['POST'])]
    public function sendContactMessage(Request $request, MailerInterface $mailer): JsonResponse
    {
        try {
            $data = json_decode($request->getContent(), true);
            
            if (!$data) {
                return new JsonResponse(['error' => 'بيانات غير صحيحة'], 400);
            }

            $name = $data['name'] ?? '';
            $email = $data['email'] ?? '';
            $subject = $data['subject'] ?? '';
            $message = $data['message'] ?? '';

            // Validate required fields
            if (empty($name) || empty($email) || empty($subject) || empty($message)) {
                return new JsonResponse(['error' => 'جميع الحقول مطلوبة'], 400);
            }

            // Validate email format
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                return new JsonResponse(['error' => 'عنوان البريد الإلكتروني غير صحيح'], 400);
            }

            // Send email
            $emailMessage = (new Email())
                ->from('shorje@abdulrhman-alshalal.com')
                ->to('shorje@abdulrhman-alshalal.com') // Admin email
                ->subject('رسالة جديدة من نموذج الاتصال: ' . $subject)
                ->html("
                    <h2>رسالة جديدة من نموذج الاتصال</h2>
                    <p><strong>الاسم:</strong> {$name}</p>
                    <p><strong>البريد الإلكتروني:</strong> {$email}</p>
                    <p><strong>الموضوع:</strong> {$subject}</p>
                    <p><strong>الرسالة:</strong></p>
                    <p>{$message}</p>
                    <hr>
                    <p><small>تم إرسال هذه الرسالة من موقع شورجي</small></p>
                ");

            $mailer->send($emailMessage);

            return new JsonResponse([
                'message' => 'تم إرسال رسالتك بنجاح. سنتواصل معك قريباً.',
                'success' => true
            ]);

        } catch (\Exception $e) {
            return new JsonResponse([
                'error' => 'حدث خطأ أثناء إرسال الرسالة: ' . $e->getMessage()
            ], 500);
        }
    }
}
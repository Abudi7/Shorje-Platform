<?php

namespace App\Controller;

use App\Service\EmailService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ContactController extends AbstractController
{
    #[Route('/contact', name: 'app_contact')]
    public function contact(): Response
    {
        return $this->render('home/contact.html.twig');
    }

    #[Route('/api/contact', name: 'api_contact', methods: ['POST'])]
    public function submitContactForm(
        Request $request,
        EmailService $emailService
    ): Response {
        $data = json_decode($request->getContent(), true);
        
        $name = $data['name'] ?? '';
        $email = $data['email'] ?? '';
        $subject = $data['subject'] ?? '';
        $message = $data['message'] ?? '';

        // Validation
        if (empty($name) || empty($email) || empty($subject) || empty($message)) {
            return $this->json(['error' => 'جميع الحقول مطلوبة'], 400);
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return $this->json(['error' => 'البريد الإلكتروني غير صالح'], 400);
        }

        try {
            // Send contact form email
            $emailService->sendContactFormEmail($name, $email, $subject, $message);
            
            return $this->json([
                'message' => 'تم إرسال رسالتك بنجاح! سنتواصل معك قريباً.'
            ]);
            
        } catch (\Exception $e) {
            error_log('Contact form error: ' . $e->getMessage());
            return $this->json([
                'error' => 'حدث خطأ أثناء إرسال الرسالة. يرجى المحاولة مرة أخرى.'
            ], 500);
        }
    }
}

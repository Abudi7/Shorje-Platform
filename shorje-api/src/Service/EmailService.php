<?php

namespace App\Service;

use App\Entity\User;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Mime\Address;
use Twig\Environment;

class EmailService
{
    public function __construct(
        private MailerInterface $mailer,
        private Environment $twig,
        private string $fromEmail,
        private string $fromName
    ) {}

    public function sendWelcomeEmail(User $user): void
    {
        $email = (new Email())
            ->from(new Address($this->fromEmail, $this->fromName))
            ->to($user->getEmail())
            ->subject('مرحباً بك في شورجي - مرحباً بك في منصتنا!')
            ->html($this->twig->render('emails/welcome.html.twig', [
                'user' => $user,
                'firstName' => $user->getFirstName(),
                'lastName' => $user->getLastName()
            ]));

        $this->mailer->send($email);
    }

    public function sendEmailVerification(User $user, string $verificationToken): void
    {
        $email = (new Email())
            ->from(new Address($this->fromEmail, $this->fromName))
            ->to($user->getEmail())
            ->subject('تأكيد البريد الإلكتروني - شورجي')
            ->html($this->twig->render('emails/email_verification.html.twig', [
                'user' => $user,
                'verificationToken' => $verificationToken,
                'verificationUrl' => $_ENV['APP_URL'] . '/verify-email?token=' . $verificationToken
            ]));

        $this->mailer->send($email);
    }

    public function sendPasswordResetEmail(User $user, string $resetToken): void
    {
        $email = (new Email())
            ->from(new Address($this->fromEmail, $this->fromName))
            ->to($user->getEmail())
            ->subject('إعادة تعيين كلمة المرور - شورجي')
            ->html($this->twig->render('emails/password_reset.html.twig', [
                'user' => $user,
                'resetToken' => $resetToken,
                'resetUrl' => $_ENV['APP_URL'] . '/reset-password?token=' . $resetToken
            ]));

        $this->mailer->send($email);
    }

    public function sendNewMessageNotification(User $user, string $senderName, string $messagePreview): void
    {
        $email = (new Email())
            ->from(new Address($this->fromEmail, $this->fromName))
            ->to($user->getEmail())
            ->subject('رسالة جديدة من ' . $senderName . ' - شورجي')
            ->html($this->twig->render('emails/new_message.html.twig', [
                'user' => $user,
                'senderName' => $senderName,
                'messagePreview' => $messagePreview,
                'messagesUrl' => $_ENV['APP_URL'] . '/messages'
            ]));

        $this->mailer->send($email);
    }

    public function sendProductNotification(User $user, string $sellerName, string $productName): void
    {
        $email = (new Email())
            ->from(new Address($this->fromEmail, $this->fromName))
            ->to($user->getEmail())
            ->subject('منتج جديد من ' . $sellerName . ' - شورجي')
            ->html($this->twig->render('emails/new_product.html.twig', [
                'user' => $user,
                'sellerName' => $sellerName,
                'productName' => $productName,
                'productsUrl' => $_ENV['APP_URL'] . '/products'
            ]));

        $this->mailer->send($email);
    }

    public function sendContactFormEmail(string $name, string $email, string $subject, string $message): void
    {
        $emailMessage = (new Email())
            ->from(new Address($this->fromEmail, $this->fromName))
            ->to($this->fromEmail) // Send to admin
            ->subject('رسالة جديدة من نموذج الاتصال: ' . $subject)
            ->html($this->twig->render('emails/contact_form.html.twig', [
                'name' => $name,
                'email' => $email,
                'subject' => $subject,
                'message' => $message
            ]));

        $this->mailer->send($emailMessage);
    }
}

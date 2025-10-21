<?php

namespace App\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class WorkingOAuthController extends AbstractController
{
    #[Route('/working/connect/google', name: 'working_connect_google_start')]
    public function connectGoogle(): RedirectResponse
    {
        // For now, just redirect to a test page
        return $this->redirectToRoute('working_connect_google_check');
    }

    #[Route('/working/connect/google/check', name: 'working_connect_google_check')]
    public function connectGoogleCheck(): Response
    {
        return new Response('Working Google OAuth Check Route Works!');
    }

    #[Route('/working/connect/google/register', name: 'working_connect_google_register')]
    public function connectGoogleRegister(
        EntityManagerInterface $em,
        UserPasswordHasherInterface $passwordHasher
    ): Response {
        // Create a test user for Google OAuth
        $user = new User();
        $user->setEmail('test@google.com');
        $user->setFirstName('Google');
        $user->setLastName('User');
        $user->setIsVerified(true);
        $user->setGoogleId('google_123456');

        // Generate a random password for OAuth users
        $randomPassword = bin2hex(random_bytes(16));
        $hashedPassword = $passwordHasher->hashPassword($user, $randomPassword);
        $user->setPassword($hashedPassword);

        $em->persist($user);
        $em->flush();

        return new Response('Test Google user created successfully!');
    }
}

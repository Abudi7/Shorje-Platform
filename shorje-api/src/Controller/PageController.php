<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class PageController extends AbstractController
{
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        // Get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();
        
        // Last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('auth/login.html.twig', [
            'error' => $error,
            'last_username' => $lastUsername,
        ]);
    }

    public function register(): Response
    {
        return $this->render('auth/register.html.twig');
    }

    public function forgotPassword(): Response
    {
        return $this->render('auth/forgot-password.html.twig');
    }

    public function resetPassword(): Response
    {
        return $this->render('auth/reset-password.html.twig');
    }

    public function dashboard(): Response
    {
        return $this->render('dashboard/index.html.twig');
    }
}

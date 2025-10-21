<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class PageController extends AbstractController
{
    public function login(): Response
    {
        return $this->render('auth/login.html.twig');
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

<?php

namespace App\Controller;

use KnpU\OAuth2ClientBundle\Client\ClientRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class SimpleOAuthController extends AbstractController
{
    #[Route('/simple/connect/google', name: 'simple_connect_google_start')]
    public function connectGoogle(ClientRegistry $clientRegistry): RedirectResponse
    {
        return $clientRegistry
            ->getClient('google')
            ->redirect(['email', 'profile']);
    }

    #[Route('/simple/connect/google/check', name: 'simple_connect_google_check')]
    public function connectGoogleCheck(): Response
    {
        return new Response('Simple Google Check Route Works!');
    }
}

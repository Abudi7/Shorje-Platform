<?php

namespace App\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class LanguageController extends AbstractController
{
    #[Route('/change-language/{locale}', name: 'app_change_language')]
    public function changeLanguage(
        string $locale, 
        Request $request,
        EntityManagerInterface $entityManager
    ): Response
    {
        // Validate locale
        $allowedLocales = ['ar', 'en'];
        if (!in_array($locale, $allowedLocales)) {
            $locale = 'ar'; // Default to Arabic
        }

        // Store the locale in the session
        $request->getSession()->set('_locale', $locale);

        // If user is logged in, save their language preference
        $user = $this->getUser();
        if ($user && method_exists($user, 'setPreferredLanguage')) {
            $user->setPreferredLanguage($locale);
            $entityManager->flush();
        }

        // Get the referer URL to redirect back
        $referer = $request->headers->get('referer');
        if ($referer) {
            return $this->redirect($referer);
        }

        // If no referer, redirect to home
        return $this->redirectToRoute('app_home_index');
    }
}

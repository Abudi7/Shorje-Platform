<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class LanguageController extends AbstractController
{
    #[Route('/language/{locale}', name: 'app_language_switch', methods: ['GET'])]
    public function switchLanguage(string $locale, Request $request, SessionInterface $session): RedirectResponse
    {
        // Validate locale
        $supportedLocales = ['ar', 'en'];
        if (!in_array($locale, $supportedLocales)) {
            $locale = 'ar'; // Default to Arabic
        }

        // Store locale in session
        $session->set('_locale', $locale);

        // Get the referer URL or default to home
        $referer = $request->headers->get('referer');
        if ($referer && strpos($referer, $request->getSchemeAndHttpHost()) === 0) {
            return $this->redirect($referer);
        }

        return $this->redirectToRoute('app_home');
    }

    #[Route('/api/language/current', name: 'api_language_current', methods: ['GET'])]
    public function getCurrentLanguage(Request $request): Response
    {
        $locale = $request->getLocale();
        
        return $this->json([
            'locale' => $locale,
            'direction' => $locale === 'ar' ? 'rtl' : 'ltr',
            'language' => $locale === 'ar' ? 'العربية' : 'English'
        ]);
    }

    #[Route('/api/language/set', name: 'api_language_set', methods: ['POST'])]
    public function setLanguage(Request $request, SessionInterface $session): Response
    {
        $data = json_decode($request->getContent(), true);
        $locale = $data['locale'] ?? 'ar';

        // Validate locale
        $supportedLocales = ['ar', 'en'];
        if (!in_array($locale, $supportedLocales)) {
            return $this->json(['error' => 'Unsupported locale'], 400);
        }

        // Store locale in session
        $session->set('_locale', $locale);

        return $this->json([
            'success' => true,
            'locale' => $locale,
            'direction' => $locale === 'ar' ? 'rtl' : 'ltr',
            'language' => $locale === 'ar' ? 'العربية' : 'English'
        ]);
    }
}

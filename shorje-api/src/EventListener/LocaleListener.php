<?php

namespace App\EventListener;

use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;

#[AsEventListener(event: KernelEvents::REQUEST, priority: 20)]
class LocaleListener
{
    public function __construct(
        private Security $security
    ) {
    }

    public function onKernelRequest(RequestEvent $event): void
    {
        $request = $event->getRequest();
        
        // Try to see if the locale has been set as a _locale routing parameter
        if ($locale = $request->attributes->get('_locale')) {
            $request->getSession()->set('_locale', $locale);
            
            // If user is logged in, update their preferred language
            $user = $this->security->getUser();
            if ($user && method_exists($user, 'setPreferredLanguage')) {
                $user->setPreferredLanguage($locale);
            }
        } else {
            // Priority: User preference > Session > Default (ar)
            $locale = 'ar';
            
            // Check if user is logged in and has a preferred language
            $user = $this->security->getUser();
            if ($user && method_exists($user, 'getPreferredLanguage')) {
                $userLocale = $user->getPreferredLanguage();
                if ($userLocale) {
                    $locale = $userLocale;
                }
            } else {
                // Fall back to session locale
                $locale = $request->getSession()->get('_locale', 'ar');
            }
            
            $request->setLocale($locale);
        }
    }
}


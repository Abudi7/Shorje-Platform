<?php

namespace App\EventListener;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\Security\Http\Event\LoginSuccessEvent;
use Symfony\Component\Security\Http\Event\LogoutEvent;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Bundle\SecurityBundle\Security;

#[AsEventListener(event: LoginSuccessEvent::class, method: 'onLoginSuccess')]
#[AsEventListener(event: LogoutEvent::class, method: 'onLogout')]
#[AsEventListener(event: RequestEvent::class, method: 'onRequest')]
class UserStatusListener
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private Security $security
    ) {}

    public function onLoginSuccess(LoginSuccessEvent $event): void
    {
        $user = $event->getUser();
        
        if ($user instanceof User) {
            $user->setIsOnline(true);
            $user->setLastSeenAt(new \DateTime('now', new \DateTimeZone('Asia/Baghdad')));
            
            $this->entityManager->persist($user);
            $this->entityManager->flush();
        }
    }

    public function onLogout(LogoutEvent $event): void
    {
        $user = $event->getToken()?->getUser();
        
        if ($user instanceof User) {
            $user->setIsOnline(false);
            $user->setLastSeenAt(new \DateTime('now', new \DateTimeZone('Asia/Baghdad')));
            
            $this->entityManager->persist($user);
            $this->entityManager->flush();
        }
    }

    public function onRequest(RequestEvent $event): void
    {
        if (!$event->isMainRequest()) {
            return;
        }

        $user = $this->security->getUser();
        
        if ($user instanceof User) {
            // Update last seen time for online users
            if ($user->isOnline()) {
                $user->setLastSeenAt(new \DateTime('now', new \DateTimeZone('Asia/Baghdad')));
                $this->entityManager->persist($user);
                $this->entityManager->flush();
            }
        }
    }
}

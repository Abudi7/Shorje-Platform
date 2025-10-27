<?php

namespace App\EventListener;

use App\Entity\SliderImage;
use App\Service\NotificationService;
use Doctrine\ORM\Event\PostPersistEventArgs;
use Doctrine\ORM\Event\PostUpdateEventArgs;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsEntityListener;
use Doctrine\ORM\Events;

#[AsEntityListener(event: Events::postPersist, entity: SliderImage::class)]
#[AsEntityListener(event: Events::postUpdate, entity: SliderImage::class)]
class SliderImageListener
{
    public function __construct(
        private NotificationService $notificationService
    ) {}

    public function postPersist(SliderImage $sliderImage, PostPersistEventArgs $args): void
    {
        $this->notifyUsers();
    }

    public function postUpdate(SliderImage $sliderImage, PostUpdateEventArgs $args): void
    {
        $this->notifyUsers();
    }

    private function notifyUsers(): void
    {
        try {
            $this->notificationService->createSliderUpdateNotification();
        } catch (\Exception $e) {
            // Log error but don't fail the operation
            error_log('Failed to create slider update notifications: ' . $e->getMessage());
        }
    }
}


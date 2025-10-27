<?php

namespace App\EventListener;

use App\Entity\Product;
use App\Service\NotificationService;
use Doctrine\ORM\Event\PostPersistEventArgs;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsEntityListener;
use Doctrine\ORM\Events;

#[AsEntityListener(event: Events::postPersist, entity: Product::class)]
class ProductListener
{
    public function __construct(
        private NotificationService $notificationService
    ) {}

    public function postPersist(Product $product, PostPersistEventArgs $args): void
    {
        // Notify followers about new product
        $seller = $product->getSeller();
        if ($seller) {
            try {
                $this->notificationService->notifyFollowersAboutNewProduct($seller, $product);
            } catch (\Exception $e) {
                // Log error but don't fail the product creation
                error_log('Failed to create product notifications: ' . $e->getMessage());
            }
        }
    }
}


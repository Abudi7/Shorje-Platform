<?php

namespace App\Service;

use App\Entity\Notification;
use App\Entity\User;
use App\Repository\NotificationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class NotificationService
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private NotificationRepository $notificationRepository,
        private UrlGeneratorInterface $urlGenerator
    ) {}

    /**
     * Create a new notification
     */
    public function createNotification(
        User $user,
        string $type,
        string $title,
        string $message,
        array $options = []
    ): Notification {
        $notification = new Notification();
        $notification->setUser($user);
        $notification->setType($type);
        $notification->setTitle($title);
        $notification->setMessage($message);

        // Set optional fields
        if (isset($options['isImportant'])) {
            $notification->setIsImportant($options['isImportant']);
        }

        if (isset($options['actionUrl'])) {
            $notification->setActionUrl($options['actionUrl']);
        }

        if (isset($options['actionText'])) {
            $notification->setActionText($options['actionText']);
        }

        if (isset($options['icon'])) {
            $notification->setIcon($options['icon']);
        }

        if (isset($options['color'])) {
            $notification->setColor($options['color']);
        }

        if (isset($options['metadata'])) {
            $notification->setMetadata($options['metadata']);
        }

        if (isset($options['expiresAt'])) {
            $notification->setExpiresAt($options['expiresAt']);
        }

        $this->entityManager->persist($notification);
        $this->entityManager->flush();

        return $notification;
    }

    /**
     * Create notification for new message
     */
    public function createMessageNotification(User $receiver, User $sender, $message): Notification
    {
        // Get message preview
        $messagePreview = is_string($message) ? $message : (method_exists($message, 'getContent') ? $message->getContent() : 'رسالة جديدة');
        
        // Truncate if too long
        if (strlen($messagePreview) > 100) {
            $messagePreview = substr($messagePreview, 0, 100) . '...';
        }
        
        // Get sender's avatar URL
        $senderImage = $sender->getAvatarUrl();
        
        return $this->createNotification(
            $receiver,
            Notification::TYPE_MESSAGE,
            'رسالة جديدة من ' . $sender->getFirstName(),
            $messagePreview,
            [
                'actionUrl' => $this->urlGenerator->generate('app_messages') . '?user=' . $sender->getId(),
                'actionText' => 'عرض الرسالة',
                'icon' => 'fas fa-envelope',
                'color' => Notification::COLOR_PRIMARY,
                'metadata' => [
                    'sender_id' => $sender->getId(),
                    'sender_name' => $sender->getFirstName() . ' ' . $sender->getLastName(),
                    'sender_image' => $senderImage,
                    'sender_email' => $sender->getEmail()
                ]
            ]
        );
    }

    /**
     * Create notification for new follow
     */
    public function createFollowNotification(User $user, User $follower): Notification
    {
        // Get follower's avatar URL
        $followerImage = $follower->getAvatarUrl();
            
        return $this->createNotification(
            $user,
            Notification::TYPE_FOLLOW,
            'متابع جديد',
            $follower->getFirstName() . ' بدأ في متابعتك',
            [
                'actionUrl' => $this->urlGenerator->generate('app_profile') . '?user=' . $follower->getId(),
                'actionText' => 'عرض الملف الشخصي',
                'icon' => 'fas fa-user-plus',
                'color' => Notification::COLOR_SUCCESS,
                'metadata' => [
                    'follower_id' => $follower->getId(),
                    'follower_name' => $follower->getFirstName() . ' ' . $follower->getLastName(),
                    'follower_image' => $followerImage,
                    'follower_email' => $follower->getEmail()
                ]
            ]
        );
    }

    /**
     * Create notification for new product from followed seller
     */
    public function createProductNotification(User $user, User $seller, string $productTitle): Notification
    {
        // Get seller's avatar URL
        $sellerImage = $seller->getAvatarUrl();
            
        return $this->createNotification(
            $user,
            Notification::TYPE_PRODUCT,
            'منتج جديد من ' . $seller->getFirstName(),
            $seller->getFirstName() . ' نشر منتج جديد: ' . $productTitle,
            [
                'actionUrl' => $this->urlGenerator->generate('app_products'),
                'actionText' => 'عرض المنتج',
                'icon' => 'fas fa-box',
                'color' => Notification::COLOR_INFO,
                'metadata' => [
                    'seller_id' => $seller->getId(),
                    'seller_name' => $seller->getFirstName() . ' ' . $seller->getLastName(),
                    'seller_image' => $sellerImage,
                    'product_title' => $productTitle
                ]
            ]
        );
    }

    /**
     * Create notification for new order
     */
    public function createOrderNotification(User $seller, User $buyer, string $productTitle): Notification
    {
        return $this->createNotification(
            $seller,
            Notification::TYPE_ORDER,
            'طلب جديد',
            $buyer->getFirstName() . ' طلب منتجك: ' . $productTitle,
            [
                'actionUrl' => $this->urlGenerator->generate('app_orders'),
                'actionText' => 'عرض الطلب',
                'icon' => 'fas fa-shopping-cart',
                'color' => Notification::COLOR_WARNING,
                'isImportant' => true,
                'metadata' => [
                    'buyer_id' => $buyer->getId(),
                    'buyer_name' => $buyer->getFirstName() . ' ' . $buyer->getLastName(),
                    'product_title' => $productTitle
                ]
            ]
        );
    }

    /**
     * Create notification for new review
     */
    public function createReviewNotification(User $seller, User $reviewer, int $rating): Notification
    {
        $stars = str_repeat('⭐', $rating);
        return $this->createNotification(
            $seller,
            Notification::TYPE_REVIEW,
            'تقييم جديد',
            $reviewer->getFirstName() . ' قيم منتجك بـ ' . $stars,
            [
                'actionUrl' => $this->urlGenerator->generate('app_reviews'),
                'actionText' => 'عرض التقييم',
                'icon' => 'fas fa-star',
                'color' => Notification::COLOR_SUCCESS,
                'metadata' => [
                    'reviewer_id' => $reviewer->getId(),
                    'reviewer_name' => $reviewer->getFirstName() . ' ' . $reviewer->getLastName(),
                    'rating' => $rating
                ]
            ]
        );
    }

    /**
     * Create system notification
     */
    public function createSystemNotification(
        User $user,
        string $title,
        string $message,
        bool $isImportant = false
    ): Notification {
        return $this->createNotification(
            $user,
            Notification::TYPE_SYSTEM,
            $title,
            $message,
            [
                'icon' => 'fas fa-info-circle',
                'color' => Notification::COLOR_INFO,
                'isImportant' => $isImportant
            ]
        );
    }

    /**
     * Create security notification
     */
    public function createSecurityNotification(User $user, string $title, string $message): Notification
    {
        return $this->createNotification(
            $user,
            Notification::TYPE_SECURITY,
            $title,
            $message,
            [
                'icon' => 'fas fa-shield-alt',
                'color' => Notification::COLOR_DANGER,
                'isImportant' => true
            ]
        );
    }

    /**
     * Create promotion notification
     */
    public function createPromotionNotification(
        User $user,
        string $title,
        string $message,
        ?string $actionUrl = null
    ): Notification {
        return $this->createNotification(
            $user,
            Notification::TYPE_PROMOTION,
            $title,
            $message,
            [
                'actionUrl' => $actionUrl,
                'actionText' => 'عرض العرض',
                'icon' => 'fas fa-gift',
                'color' => Notification::COLOR_SUCCESS,
                'expiresAt' => (new \DateTime('now', new \DateTimeZone('Asia/Baghdad')))->modify('+7 days')
            ]
        );
    }

    /**
     * Mark notification as read
     */
    public function markAsRead(Notification $notification): void
    {
        $notification->markAsRead();
        $this->entityManager->flush();
    }

    /**
     * Delete a notification
     */
    public function deleteNotification(Notification $notification): void
    {
        $this->entityManager->remove($notification);
        $this->entityManager->flush();
    }

    /**
     * Mark multiple notifications as read
     */
    public function markAsReadByIds(array $ids, User $user): int
    {
        return $this->notificationRepository->markAsReadByIds($ids, $user);
    }

    /**
     * Mark all notifications as read for a user
     */
    public function markAllAsRead(User $user): int
    {
        return $this->notificationRepository->markAllAsRead($user);
    }

    /**
     * Get notifications for user
     */
    public function getUserNotifications(User $user, int $limit = 50, int $offset = 0): array
    {
        return $this->notificationRepository->findByUser($user, $limit, $offset);
    }

    /**
     * Get unread notifications count
     */
    public function getUnreadCount(User $user): int
    {
        return $this->notificationRepository->getUnreadCount($user);
    }

    /**
     * Get unread notifications
     */
    public function getUnreadNotifications(User $user, int $limit = 20): array
    {
        return $this->notificationRepository->getUnreadNotifications($user, $limit);
    }

    /**
     * Get notifications by type
     */
    public function getNotificationsByType(User $user, string $type, int $limit = 20): array
    {
        return $this->notificationRepository->findByUserAndType($user, $type, $limit);
    }

    /**
     * Get notification statistics
     */
    public function getNotificationStats(User $user): array
    {
        return $this->notificationRepository->getNotificationStats($user);
    }

    /**
     * Clean up expired notifications
     */
    public function cleanupExpired(): int
    {
        return $this->notificationRepository->cleanupExpired();
    }

    /**
     * Bulk create notifications for multiple users
     */
    public function createBulkNotifications(
        array $users,
        string $type,
        string $title,
        string $message,
        array $options = []
    ): array {
        $notifications = [];
        foreach ($users as $user) {
            $notifications[] = $this->createNotification($user, $type, $title, $message, $options);
        }
        return $notifications;
    }

    /**
     * Create platform update notification for all users
     */
    public function createPlatformUpdateNotification(
        string $title,
        string $message,
        ?string $actionUrl = null
    ): array {
        // Get all active users
        $users = $this->entityManager->getRepository(User::class)->findAll();
        
        return $this->createBulkNotifications(
            $users,
            Notification::TYPE_SYSTEM,
            $title,
            $message,
            [
                'actionUrl' => $actionUrl,
                'actionText' => 'Learn More',
                'icon' => 'fas fa-bullhorn',
                'color' => Notification::COLOR_INFO,
                'isImportant' => true
            ]
        );
    }

    /**
     * Create slider image update notification for all users
     */
    public function createSliderUpdateNotification(): array {
        return $this->createPlatformUpdateNotification(
            'New Images in Gallery',
            'Check out our new images in the home page slider!',
            $this->urlGenerator->generate('app_home')
        );
    }

    /**
     * Notify followers when seller posts a new product
     */
    public function notifyFollowersAboutNewProduct(User $seller, $product): array {
        // Get all followers of this seller
        $followRepo = $this->entityManager->getRepository(\App\Entity\Follow::class);
        $followers = $followRepo->createQueryBuilder('f')
            ->select('IDENTITY(f.follower)')
            ->where('f.following = :seller')
            ->setParameter('seller', $seller)
            ->getQuery()
            ->getScalarResult();
        
        $followerIds = array_column($followers, 1);
        $followerUsers = $this->entityManager->getRepository(User::class)->findBy(['id' => $followerIds]);
        
        $productTitle = is_string($product) ? $product : (method_exists($product, 'getTitle') ? $product->getTitle() : 'New Product');
        $productId = is_object($product) && method_exists($product, 'getId') ? $product->getId() : null;
        
        $actionUrl = $productId 
            ? $this->urlGenerator->generate('app_product_show', ['id' => $productId])
            : $this->urlGenerator->generate('app_products');
        
        return $this->createBulkNotifications(
            $followerUsers,
            Notification::TYPE_PRODUCT,
            'New Product from ' . $seller->getFirstName(),
            $seller->getFirstName() . ' ' . $seller->getLastName() . ' posted: ' . $productTitle,
            [
                'actionUrl' => $actionUrl,
                'actionText' => 'View Product',
                'icon' => 'fas fa-box',
                'color' => Notification::COLOR_SUCCESS,
                'metadata' => [
                    'seller_id' => $seller->getId(),
                    'seller_name' => $seller->getFirstName() . ' ' . $seller->getLastName(),
                    'product_title' => $productTitle,
                    'product_id' => $productId
                ]
            ]
        );
    }

    /**
     * Create notification with automatic icon and color based on type
     */
    public function createTypedNotification(
        User $user,
        string $type,
        string $title,
        string $message,
        array $options = []
    ): Notification {
        $typeConfig = $this->getTypeConfiguration($type);
        
        $options = array_merge($typeConfig, $options);
        
        return $this->createNotification($user, $type, $title, $message, $options);
    }

    /**
     * Get default configuration for notification types
     */
    private function getTypeConfiguration(string $type): array
    {
        $configs = [
            Notification::TYPE_MESSAGE => [
                'icon' => 'fas fa-envelope',
                'color' => Notification::COLOR_PRIMARY
            ],
            Notification::TYPE_FOLLOW => [
                'icon' => 'fas fa-user-plus',
                'color' => Notification::COLOR_SUCCESS
            ],
            Notification::TYPE_PRODUCT => [
                'icon' => 'fas fa-box',
                'color' => Notification::COLOR_INFO
            ],
            Notification::TYPE_ORDER => [
                'icon' => 'fas fa-shopping-cart',
                'color' => Notification::COLOR_WARNING,
                'isImportant' => true
            ],
            Notification::TYPE_REVIEW => [
                'icon' => 'fas fa-star',
                'color' => Notification::COLOR_SUCCESS
            ],
            Notification::TYPE_SYSTEM => [
                'icon' => 'fas fa-info-circle',
                'color' => Notification::COLOR_INFO
            ],
            Notification::TYPE_SECURITY => [
                'icon' => 'fas fa-shield-alt',
                'color' => Notification::COLOR_DANGER,
                'isImportant' => true
            ],
            Notification::TYPE_PROMOTION => [
                'icon' => 'fas fa-gift',
                'color' => Notification::COLOR_SUCCESS
            ]
        ];

        return $configs[$type] ?? [];
    }
}

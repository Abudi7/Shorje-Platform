<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\Product;
use App\Entity\Follow;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class NotificationsController extends AbstractController
{
    #[Route('/notifications', name: 'notifications_page', methods: ['GET'])]
    public function notificationsPage(): Response
    {
        $user = $this->getUser();
        if (!$user) {
            throw new AccessDeniedException('يجب تسجيل الدخول أولاً');
        }

        return $this->render('notifications/index.html.twig', [
            'user' => $user
        ]);
    }

    #[Route('/web/notifications', name: 'web_notifications_list', methods: ['GET'])]
    public function getNotifications(EntityManagerInterface $em): JsonResponse
    {
        $user = $this->getUser();
        if (!$user) {
            return new JsonResponse(['error' => 'يجب تسجيل الدخول أولاً'], 401);
        }

        // Get all users that the current user follows
        $followedUsers = $em->getRepository(Follow::class)->findBy(['follower' => $user]);
        
        $notifications = [];
        
        foreach ($followedUsers as $follow) {
            $followedUser = $follow->getFollowing();
            
            // Get recent products from this followed user (last 30 days)
            $recentProducts = $em->getRepository(Product::class)
                ->createQueryBuilder('p')
                ->where('p.seller = :seller')
                ->andWhere('p.createdAt >= :date')
                ->setParameter('seller', $followedUser)
                ->setParameter('date', new \DateTime('-30 days'))
                ->orderBy('p.createdAt', 'DESC')
                ->getQuery()
                ->getResult();
            
            foreach ($recentProducts as $product) {
                $notifications[] = [
                    'id' => $product->getId(),
                    'type' => 'new_product',
                    'title' => 'منتج جديد من ' . $followedUser->getFullName(),
                    'message' => $product->getTitle(),
                    'product' => [
                        'id' => $product->getId(),
                        'title' => $product->getTitle(),
                        'price' => $product->getPrice(),
                        'currency' => $product->getCurrency(),
                        'currencyDisplay' => $product->getCurrencyDisplayName(),
                        'category' => $product->getCategory(),
                        'categoryDisplay' => $product->getCategoryDisplayName(),
                        'city' => $product->getCity(),
                        'status' => $product->getStatus(),
                        'statusDisplay' => $product->getStatusDisplayName(),
                        'createdAt' => $product->getCreatedAt()->format('Y-m-d H:i:s'),
                        'hasImages' => [
                            'image1' => $product->getImage1() !== null,
                            'image2' => $product->getImage2() !== null,
                            'image3' => $product->getImage3() !== null
                        ]
                    ],
                    'seller' => [
                        'id' => $followedUser->getId(),
                        'name' => $followedUser->getFullName(),
                        'email' => $followedUser->getEmail()
                    ],
                    'createdAt' => $product->getCreatedAt()->format('Y-m-d H:i:s'),
                    'isRead' => false // You can implement read status later
                ];
            }
        }

        // Sort notifications by creation date (newest first)
        usort($notifications, function($a, $b) {
            return strtotime($b['createdAt']) - strtotime($a['createdAt']);
        });

        return new JsonResponse([
            'notifications' => $notifications,
            'total' => count($notifications),
            'unreadCount' => count($notifications) // For now, all are unread
        ]);
    }

    #[Route('/web/notifications/{id}/read', name: 'web_notification_mark_read', methods: ['POST'])]
    public function markNotificationAsRead(int $id, EntityManagerInterface $em): JsonResponse
    {
        $user = $this->getUser();
        if (!$user) {
            return new JsonResponse(['error' => 'يجب تسجيل الدخول أولاً'], 401);
        }

        // For now, we'll just return success
        // You can implement actual read status tracking later
        return new JsonResponse(['message' => 'تم تحديد الإشعار كمقروء']);
    }

    #[Route('/web/notifications/mark-all-read', name: 'web_notifications_mark_all_read', methods: ['POST'])]
    public function markAllNotificationsAsRead(EntityManagerInterface $em): JsonResponse
    {
        $user = $this->getUser();
        if (!$user) {
            return new JsonResponse(['error' => 'يجب تسجيل الدخول أولاً'], 401);
        }

        // For now, we'll just return success
        // You can implement actual read status tracking later
        return new JsonResponse(['message' => 'تم تحديد جميع الإشعارات كمقروءة']);
    }
}

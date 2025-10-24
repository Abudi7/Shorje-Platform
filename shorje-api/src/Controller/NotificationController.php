<?php

namespace App\Controller;

use App\Entity\Notification;
use App\Entity\User;
use App\Service\NotificationService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/app/notifications')]
class NotificationController extends AbstractController
{
    public function __construct(
        private NotificationService $notificationService
    ) {}

    #[Route('/page', name: 'app_notifications_page', methods: ['GET'])]
    #[IsGranted('ROLE_USER')]
    public function index(): Response
    {
        return $this->render('notifications/index.html.twig');
    }


    #[Route('', name: 'app_notifications_list', methods: ['GET'])]
    #[IsGranted('ROLE_USER')]
    public function list(Request $request): JsonResponse
    {
        $user = $this->getUser();
        $limit = (int) $request->query->get('limit', 50);
        $offset = (int) $request->query->get('offset', 0);
        $type = $request->query->get('type');

        if ($type) {
            $notifications = $this->notificationService->getNotificationsByType($user, $type, $limit);
        } else {
            $notifications = $this->notificationService->getUserNotifications($user, $limit, $offset);
        }

        // Format notifications for frontend
        $formattedNotifications = array_map(function($notification) {
            return [
                'id' => $notification->getId(),
                'type' => $notification->getType(),
                'title' => $notification->getTitle(),
                'message' => $notification->getMessage(),
                'icon' => $notification->getIcon() ?: 'fas fa-bell',
                'color' => $notification->getColor() ?: 'primary',
                'actionUrl' => $notification->getActionUrl(),
                'actionText' => $notification->getActionText(),
                'isRead' => $notification->isRead(),
                'isImportant' => $notification->isImportant(),
                'createdAt' => $notification->getCreatedAt()->format('Y-m-d H:i:s'),
                'readAt' => $notification->getReadAt() ? $notification->getReadAt()->format('Y-m-d H:i:s') : null,
                'metadata' => $notification->getMetadata()
            ];
        }, $notifications);

        return $this->json([
            'notifications' => $formattedNotifications,
            'pagination' => [
                'limit' => $limit,
                'offset' => $offset,
                'total' => count($formattedNotifications)
            ]
        ]);
    }

    #[Route('/unread', name: 'app_notifications_unread', methods: ['GET'])]
    #[IsGranted('ROLE_USER')]
    public function unread(Request $request): JsonResponse
    {
        try {
            $user = $this->getUser();
            if (!$user) {
                return $this->json(['error' => 'User not authenticated'], Response::HTTP_UNAUTHORIZED);
            }

            $limit = (int) $request->query->get('limit', 20);
            $notifications = $this->notificationService->getUnreadNotifications($user, $limit);
            $unreadCount = $this->notificationService->getUnreadCount($user);

            // Format notifications for frontend
            $formattedNotifications = [];
            if (is_array($notifications)) {
                foreach ($notifications as $notification) {
                    try {
                        $formattedNotifications[] = [
                            'id' => $notification->getId(),
                            'type' => $notification->getType(),
                            'title' => $notification->getTitle(),
                            'message' => $notification->getMessage(),
                            'icon' => $notification->getIcon() ?: 'fas fa-bell',
                            'color' => $notification->getColor() ?: 'primary',
                            'actionUrl' => $notification->getActionUrl(),
                            'actionText' => $notification->getActionText(),
                            'isRead' => $notification->isRead(),
                            'isImportant' => $notification->isImportant(),
                            'createdAt' => $notification->getCreatedAt()->format('Y-m-d H:i:s'),
                            'readAt' => $notification->getReadAt() ? $notification->getReadAt()->format('Y-m-d H:i:s') : null,
                            'metadata' => $notification->getMetadata()
                        ];
                    } catch (\Exception $e) {
                        // Skip this notification if there's an error formatting it
                        continue;
                    }
                }
            }

            return $this->json([
                'notifications' => $formattedNotifications,
                'unread_count' => $unreadCount
            ]);
        } catch (\Exception $e) {
            return $this->json([
                'error' => 'Failed to load notifications',
                'message' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/unread-count', name: 'app_notifications_unread_count', methods: ['GET'])]
    public function unreadCount(): JsonResponse
    {
        $user = $this->getUser();
        
        // Return 0 if user is not authenticated
        if (!$user) {
            return $this->json(['unread_count' => 0, 'count' => 0]);
        }
        
        $count = $this->notificationService->getUnreadCount($user);

        return $this->json(['unread_count' => $count, 'count' => $count]);
    }

    #[Route('/stats', name: 'app_notifications_stats', methods: ['GET'])]
    #[IsGranted('ROLE_USER')]
    public function stats(): JsonResponse
    {
        $user = $this->getUser();
        $stats = $this->notificationService->getNotificationStats($user);

        return $this->json($stats);
    }

    #[Route('/{id}/read', name: 'app_notification_mark_read', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    public function markAsRead(Notification $notification): JsonResponse
    {
        $user = $this->getUser();

        // Ensure user can only mark their own notifications as read
        if ($notification->getUser() !== $user) {
            return $this->json(['error' => 'Unauthorized'], Response::HTTP_FORBIDDEN);
        }

        $this->notificationService->markAsRead($notification);

        return $this->json(['message' => 'Notification marked as read']);
    }

    #[Route('/mark-read', name: 'app_notifications_mark_read_bulk', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    public function markAsReadBulk(Request $request): JsonResponse
    {
        $user = $this->getUser();
        $data = json_decode($request->getContent(), true);
        $ids = $data['ids'] ?? [];

        if (empty($ids)) {
            return $this->json(['error' => 'No notification IDs provided'], Response::HTTP_BAD_REQUEST);
        }

        $count = $this->notificationService->markAsReadByIds($ids, $user);

        return $this->json([
            'message' => "Marked {$count} notifications as read"
        ]);
    }

    #[Route('/mark-all-read', name: 'app_notifications_mark_all_read', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    public function markAllAsRead(): JsonResponse
    {
        $user = $this->getUser();
        $count = $this->notificationService->markAllAsRead($user);

        return $this->json([
            'message' => "Marked {$count} notifications as read"
        ]);
    }

    #[Route('/{id}', name: 'app_notification_show', methods: ['GET'])]
    #[IsGranted('ROLE_USER')]
    public function show(Notification $notification): JsonResponse
    {
        $user = $this->getUser();

        // Ensure user can only view their own notifications
        if ($notification->getUser() !== $user) {
            return $this->json(['error' => 'Unauthorized'], Response::HTTP_FORBIDDEN);
        }

        // Format notification for frontend
        $formattedNotification = [
            'id' => $notification->getId(),
            'type' => $notification->getType(),
            'title' => $notification->getTitle(),
            'message' => $notification->getMessage(),
            'icon' => $notification->getIcon() ?: 'fas fa-bell',
            'color' => $notification->getColor() ?: 'primary',
            'actionUrl' => $notification->getActionUrl(),
            'actionText' => $notification->getActionText(),
            'isRead' => $notification->isRead(),
            'isImportant' => $notification->isImportant(),
            'createdAt' => $notification->getCreatedAt()->format('Y-m-d H:i:s'),
            'readAt' => $notification->getReadAt() ? $notification->getReadAt()->format('Y-m-d H:i:s') : null,
            'metadata' => $notification->getMetadata()
        ];

        return $this->json($formattedNotification);
    }

    #[Route('/{id}', name: 'app_notification_delete', methods: ['DELETE'])]
    #[IsGranted('ROLE_USER')]
    public function delete(Notification $notification): JsonResponse
    {
        $user = $this->getUser();

        // Ensure user can only delete their own notifications
        if ($notification->getUser() !== $user) {
            return $this->json(['error' => 'Unauthorized'], Response::HTTP_FORBIDDEN);
        }

        $this->notificationService->deleteNotification($notification);

        return $this->json(['message' => 'Notification deleted']);
    }

    #[Route('/cleanup', name: 'app_notifications_cleanup', methods: ['POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function cleanup(): JsonResponse
    {
        $count = $this->notificationService->cleanupExpired();

        return $this->json([
            'message' => "Cleaned up {$count} expired notifications"
        ]);
    }

    #[Route('/create', name: 'app_notification_create', methods: ['POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function create(Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $requiredFields = ['user_id', 'type', 'title', 'message'];
        foreach ($requiredFields as $field) {
            if (!isset($data[$field])) {
                return $this->json(['error' => "Missing required field: {$field}"], Response::HTTP_BAD_REQUEST);
            }
        }

        $user = $entityManager->getRepository(User::class)->find($data['user_id']);
        if (!$user) {
            return $this->json(['error' => 'User not found'], Response::HTTP_NOT_FOUND);
        }

        $options = $data['options'] ?? [];
        $notification = $this->notificationService->createNotification(
            $user,
            $data['type'],
            $data['title'],
            $data['message'],
            $options
        );

        // Format notification for frontend
        $formattedNotification = [
            'id' => $notification->getId(),
            'type' => $notification->getType(),
            'title' => $notification->getTitle(),
            'message' => $notification->getMessage(),
            'icon' => $notification->getIcon() ?: 'fas fa-bell',
            'color' => $notification->getColor() ?: 'primary',
            'actionUrl' => $notification->getActionUrl(),
            'actionText' => $notification->getActionText(),
            'isRead' => $notification->isRead(),
            'isImportant' => $notification->isImportant(),
            'createdAt' => $notification->getCreatedAt()->format('Y-m-d H:i:s'),
            'readAt' => $notification->getReadAt() ? $notification->getReadAt()->format('Y-m-d H:i:s') : null,
            'metadata' => $notification->getMetadata()
        ];

        return $this->json($formattedNotification, Response::HTTP_CREATED);
    }
}

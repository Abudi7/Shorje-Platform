<?php

namespace App\Controller;

use App\Entity\Notification;
use App\Entity\User;
use App\Service\NotificationService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/api/notifications')]
class NotificationController extends AbstractController
{
    public function __construct(
        private NotificationService $notificationService
    ) {}

    #[Route('/page', name: 'app_notifications', methods: ['GET'])]
    #[IsGranted('ROLE_USER')]
    public function index(): Response
    {
        return $this->render('notifications/index.html.twig');
    }

    #[Route('', name: 'api_notifications_list', methods: ['GET'])]
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

        return $this->json([
            'notifications' => $notifications,
            'pagination' => [
                'limit' => $limit,
                'offset' => $offset,
                'total' => count($notifications)
            ]
        ]);
    }

    #[Route('/unread', name: 'api_notifications_unread', methods: ['GET'])]
    #[IsGranted('ROLE_USER')]
    public function unread(Request $request): JsonResponse
    {
        $user = $this->getUser();
        $limit = (int) $request->query->get('limit', 20);

        $notifications = $this->notificationService->getUnreadNotifications($user, $limit);
        $unreadCount = $this->notificationService->getUnreadCount($user);

        return $this->json([
            'notifications' => $notifications,
            'unread_count' => $unreadCount
        ]);
    }

    #[Route('/unread-count', name: 'api_notifications_unread_count', methods: ['GET'])]
    #[IsGranted('ROLE_USER')]
    public function unreadCount(): JsonResponse
    {
        $user = $this->getUser();
        $count = $this->notificationService->getUnreadCount($user);

        return $this->json(['unread_count' => $count]);
    }

    #[Route('/stats', name: 'api_notifications_stats', methods: ['GET'])]
    #[IsGranted('ROLE_USER')]
    public function stats(): JsonResponse
    {
        $user = $this->getUser();
        $stats = $this->notificationService->getNotificationStats($user);

        return $this->json($stats);
    }

    #[Route('/{id}/read', name: 'api_notification_mark_read', methods: ['POST'])]
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

    #[Route('/mark-read', name: 'api_notifications_mark_read_bulk', methods: ['POST'])]
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

    #[Route('/mark-all-read', name: 'api_notifications_mark_all_read', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    public function markAllAsRead(): JsonResponse
    {
        $user = $this->getUser();
        $count = $this->notificationService->markAllAsRead($user);

        return $this->json([
            'message' => "Marked {$count} notifications as read"
        ]);
    }

    #[Route('/{id}', name: 'api_notification_show', methods: ['GET'])]
    #[IsGranted('ROLE_USER')]
    public function show(Notification $notification): JsonResponse
    {
        $user = $this->getUser();

        // Ensure user can only view their own notifications
        if ($notification->getUser() !== $user) {
            return $this->json(['error' => 'Unauthorized'], Response::HTTP_FORBIDDEN);
        }

        return $this->json($notification);
    }

    #[Route('/{id}', name: 'api_notification_delete', methods: ['DELETE'])]
    #[IsGranted('ROLE_USER')]
    public function delete(Notification $notification): JsonResponse
    {
        $user = $this->getUser();

        // Ensure user can only delete their own notifications
        if ($notification->getUser() !== $user) {
            return $this->json(['error' => 'Unauthorized'], Response::HTTP_FORBIDDEN);
        }

        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->remove($notification);
        $entityManager->flush();

        return $this->json(['message' => 'Notification deleted']);
    }

    #[Route('/cleanup', name: 'api_notifications_cleanup', methods: ['POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function cleanup(): JsonResponse
    {
        $count = $this->notificationService->cleanupExpired();

        return $this->json([
            'message' => "Cleaned up {$count} expired notifications"
        ]);
    }

    #[Route('/create', name: 'api_notification_create', methods: ['POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function create(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $requiredFields = ['user_id', 'type', 'title', 'message'];
        foreach ($requiredFields as $field) {
            if (!isset($data[$field])) {
                return $this->json(['error' => "Missing required field: {$field}"], Response::HTTP_BAD_REQUEST);
            }
        }

        $user = $this->getDoctrine()->getRepository(User::class)->find($data['user_id']);
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

        return $this->json($notification, Response::HTTP_CREATED);
    }
}

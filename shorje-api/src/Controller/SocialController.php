<?php

namespace App\Controller;

use App\Entity\Follow;
use App\Entity\Message;
use App\Entity\User;
use App\Repository\FollowRepository;
use App\Repository\MessageRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class SocialController extends AbstractController
{
    #[Route('/api/follow/{userId}', name: 'api_follow_user', methods: ['POST'])]
    public function followUser(int $userId, EntityManagerInterface $em, FollowRepository $followRepo): JsonResponse
    {
        try {
            $currentUser = $this->getUser();
            if (!$currentUser) {
                return new JsonResponse(['error' => 'يجب تسجيل الدخول أولاً'], 401);
            }

            $userToFollow = $em->getRepository(User::class)->find($userId);
            if (!$userToFollow) {
                return new JsonResponse(['error' => 'المستخدم غير موجود'], 404);
            }

            if ($currentUser->getId() === $userId) {
                return new JsonResponse(['error' => 'لا يمكنك متابعة نفسك'], 400);
            }

            if ($followRepo->isFollowing($currentUser, $userToFollow)) {
                return new JsonResponse(['error' => 'أنت تتابع هذا المستخدم بالفعل'], 400);
            }

            $follow = new Follow();
            $follow->setFollower($currentUser);
            $follow->setFollowing($userToFollow);

            $em->persist($follow);
            $em->flush();

            return new JsonResponse([
                'message' => 'تم متابعة المستخدم بنجاح',
                'followersCount' => $followRepo->getFollowersCount($userToFollow),
                'success' => true
            ]);
        } catch (\Exception $e) {
            return new JsonResponse([
                'error' => 'حدث خطأ أثناء المتابعة: ' . $e->getMessage()
            ], 500);
        }
    }

    #[Route('/api/unfollow/{userId}', name: 'api_unfollow_user', methods: ['POST'])]
    public function unfollowUser(int $userId, EntityManagerInterface $em, FollowRepository $followRepo): JsonResponse
    {
        try {
            $currentUser = $this->getUser();
            if (!$currentUser) {
                return new JsonResponse(['error' => 'يجب تسجيل الدخول أولاً'], 401);
            }

            $userToUnfollow = $em->getRepository(User::class)->find($userId);
            if (!$userToUnfollow) {
                return new JsonResponse(['error' => 'المستخدم غير موجود'], 404);
            }

            $follow = $followRepo->createQueryBuilder('f')
                ->where('f.follower = :follower')
                ->andWhere('f.following = :following')
                ->setParameter('follower', $currentUser)
                ->setParameter('following', $userToUnfollow)
                ->getQuery()
                ->getOneOrNullResult();

            if (!$follow) {
                return new JsonResponse(['error' => 'أنت لا تتابع هذا المستخدم'], 400);
            }

            $em->remove($follow);
            $em->flush();

            return new JsonResponse([
                'message' => 'تم إلغاء متابعة المستخدم بنجاح',
                'followersCount' => $followRepo->getFollowersCount($userToUnfollow),
                'success' => true
            ]);
        } catch (\Exception $e) {
            return new JsonResponse([
                'error' => 'حدث خطأ أثناء إلغاء المتابعة: ' . $e->getMessage()
            ], 500);
        }
    }

    #[Route('/api/follow-status/{userId}', name: 'api_follow_status', methods: ['GET'])]
    public function getFollowStatus(int $userId, EntityManagerInterface $em, FollowRepository $followRepo): JsonResponse
    {
        try {
            $currentUser = $this->getUser();
            if (!$currentUser) {
                return new JsonResponse(['error' => 'يجب تسجيل الدخول أولاً'], 401);
            }

            $user = $em->getRepository(User::class)->find($userId);
            if (!$user) {
                return new JsonResponse(['error' => 'المستخدم غير موجود'], 404);
            }

            $isFollowing = $followRepo->isFollowing($currentUser, $user);
            $followersCount = $followRepo->getFollowersCount($user);
            $followingCount = $followRepo->getFollowingCount($user);

            return new JsonResponse([
                'isFollowing' => $isFollowing,
                'followersCount' => $followersCount,
                'followingCount' => $followingCount,
                'success' => true
            ]);
        } catch (\Exception $e) {
            return new JsonResponse([
                'error' => 'حدث خطأ أثناء جلب حالة المتابعة: ' . $e->getMessage()
            ], 500);
        }
    }

    #[Route('/api/send-message', name: 'api_send_message', methods: ['POST'])]
    public function sendMessage(Request $request, EntityManagerInterface $em): JsonResponse
    {
        $currentUser = $this->getUser();
        if (!$currentUser) {
            return new JsonResponse(['error' => 'يجب تسجيل الدخول أولاً'], 401);
        }

        $data = json_decode($request->getContent(), true);
        $receiverId = $data['receiverId'] ?? null;
        $content = $data['content'] ?? null;

        if (!$receiverId || !$content) {
            return new JsonResponse(['error' => 'المعرف والمحتوى مطلوبان'], 400);
        }

        $receiver = $em->getRepository(User::class)->find($receiverId);
        if (!$receiver) {
            return new JsonResponse(['error' => 'المستخدم غير موجود'], 404);
        }

        if ($currentUser->getId() === $receiverId) {
            return new JsonResponse(['error' => 'لا يمكنك إرسال رسالة لنفسك'], 400);
        }

        $message = new Message();
        $message->setSender($currentUser);
        $message->setReceiver($receiver);
        $message->setContent($content);

        $em->persist($message);
        $em->flush();

        return new JsonResponse([
            'message' => 'تم إرسال الرسالة بنجاح',
            'messageId' => $message->getId(),
            'createdAt' => $message->getCreatedAt()->format('Y-m-d H:i:s')
        ]);
    }

    #[Route('/api/conversation/{userId}', name: 'api_get_conversation', methods: ['GET'])]
    public function getConversation(int $userId, EntityManagerInterface $em, MessageRepository $messageRepo): JsonResponse
    {
        $currentUser = $this->getUser();
        if (!$currentUser) {
            return new JsonResponse(['error' => 'يجب تسجيل الدخول أولاً'], 401);
        }

        $otherUser = $em->getRepository(User::class)->find($userId);
        if (!$otherUser) {
            return new JsonResponse(['error' => 'المستخدم غير موجود'], 404);
        }

        $messages = $messageRepo->findConversation($currentUser, $otherUser);
        $messageRepo->markMessagesAsRead($currentUser, $otherUser);

        $messagesData = [];
        foreach ($messages as $message) {
            $messagesData[] = [
                'id' => $message->getId(),
                'content' => $message->getContent(),
                'senderId' => $message->getSender()->getId(),
                'senderName' => $message->getSender()->getFullName(),
                'isRead' => $message->isRead(),
                'createdAt' => $message->getCreatedAt()->format('Y-m-d H:i:s')
            ];
        }

        return new JsonResponse([
            'messages' => $messagesData,
            'otherUser' => [
                'id' => $otherUser->getId(),
                'name' => $otherUser->getFullName(),
                'email' => $otherUser->getEmail()
            ]
        ]);
    }

    #[Route('/api/conversations', name: 'api_get_conversations', methods: ['GET'])]
    public function getConversations(MessageRepository $messageRepo): JsonResponse
    {
        $currentUser = $this->getUser();
        if (!$currentUser) {
            return new JsonResponse(['error' => 'يجب تسجيل الدخول أولاً'], 401);
        }

        $conversations = $messageRepo->findRecentConversations($currentUser);
        $unreadCount = $messageRepo->getUnreadMessagesCount($currentUser);

        return new JsonResponse([
            'conversations' => $conversations,
            'unreadCount' => $unreadCount
        ]);
    }

    #[Route('/api/notifications', name: 'api_get_notifications', methods: ['GET'])]
    public function getNotifications(MessageRepository $messageRepo): JsonResponse
    {
        $currentUser = $this->getUser();
        if (!$currentUser) {
            return new JsonResponse(['error' => 'يجب تسجيل الدخول أولاً'], 401);
        }

        // Get unread messages count
        $unreadCount = $messageRepo->getUnreadMessagesCount($currentUser);
        
        // Get recent unread messages with sender info
        $unreadMessages = $messageRepo->createQueryBuilder('m')
            ->join('m.sender', 's')
            ->where('m.receiver = :user')
            ->andWhere('m.isRead = false')
            ->setParameter('user', $currentUser)
            ->orderBy('m.createdAt', 'DESC')
            ->setMaxResults(5)
            ->getQuery()
            ->getResult();

        $notifications = [];
        foreach ($unreadMessages as $message) {
            $sender = $message->getSender();
            
            $notifications[] = [
                'id' => $message->getId(),
                'type' => 'message',
                'message' => 'رسالة جديدة من ' . $sender->getFullName(),
                'content' => $message->getContent(),
                'senderId' => $sender->getId(),
                'senderName' => $sender->getFullName(),
                'timestamp' => $message->getCreatedAt()->format('Y-m-d H:i:s'),
                'isRead' => $message->isRead()
            ];
        }

        return new JsonResponse([
            'unreadCount' => $unreadCount,
            'notifications' => $notifications,
            'hasNewMessages' => $unreadCount > 0
        ]);
    }

    #[Route('/api/notifications/mark-all-read', name: 'api_mark_all_notifications_read', methods: ['POST'])]
    public function markAllNotificationsAsRead(MessageRepository $messageRepo): JsonResponse
    {
        $currentUser = $this->getUser();
        if (!$currentUser) {
            return new JsonResponse(['error' => 'يجب تسجيل الدخول أولاً'], 401);
        }

        // Mark all unread messages as read
        $messageRepo->createQueryBuilder('m')
            ->update()
            ->set('m.isRead', true)
            ->where('m.receiver = :user')
            ->andWhere('m.isRead = false')
            ->setParameter('user', $currentUser)
            ->getQuery()
            ->execute();

        return new JsonResponse(['message' => 'تم تحديد جميع الإشعارات كمقروءة']);
    }

    #[Route('/api/conversation/{userId}/mark-read', name: 'api_mark_conversation_read', methods: ['POST'])]
    public function markConversationAsRead(int $userId, MessageRepository $messageRepo, EntityManagerInterface $em): JsonResponse
    {
        $currentUser = $this->getUser();
        if (!$currentUser) {
            return new JsonResponse(['error' => 'يجب تسجيل الدخول أولاً'], 401);
        }

        $otherUser = $em->getRepository(User::class)->find($userId);
        if (!$otherUser) {
            return new JsonResponse(['error' => 'المستخدم غير موجود'], 404);
        }

        // Mark all messages from this user as read
        $messageRepo->markMessagesAsRead($currentUser, $otherUser);

        return new JsonResponse(['message' => 'تم تحديد المحادثة كمقروءة']);
    }

    #[Route('/users', name: 'app_users')]
    public function users(EntityManagerInterface $em): Response
    {
        $currentUser = $this->getUser();
        if (!$currentUser) {
            throw new AccessDeniedException('يجب تسجيل الدخول أولاً');
        }

        $users = $em->getRepository(User::class)->createQueryBuilder('u')
            ->where('u.id != :currentUserId')
            ->setParameter('currentUserId', $currentUser->getId())
            ->orderBy('u.firstName', 'ASC')
            ->getQuery()
            ->getResult();

        return $this->render('social/users.html.twig', [
            'users' => $users,
            'currentUser' => $currentUser
        ]);
    }

    #[Route('/chat/{userId}', name: 'app_chat')]
    public function chat(int $userId, EntityManagerInterface $em, MessageRepository $messageRepo): Response
    {
        $currentUser = $this->getUser();
        if (!$currentUser) {
            throw new AccessDeniedException('يجب تسجيل الدخول أولاً');
        }

        $otherUser = $em->getRepository(User::class)->find($userId);
        if (!$otherUser) {
            throw $this->createNotFoundException('المستخدم غير موجود');
        }

        // Get conversation messages
        $messages = $messageRepo->findConversation($currentUser, $otherUser);

        return $this->render('social/chat.html.twig', [
            'currentUser' => $currentUser,
            'otherUser' => $otherUser,
            'messages' => $messages
        ]);
    }

    #[Route('/api/search-sellers', name: 'api_search_sellers', methods: ['GET'])]
    public function searchSellers(Request $request, EntityManagerInterface $em): JsonResponse
    {
        $currentUser = $this->getUser();
        if (!$currentUser) {
            return new JsonResponse(['error' => 'يجب تسجيل الدخول أولاً'], 401);
        }

        $query = $request->query->get('q', '');
        if (empty($query)) {
            return new JsonResponse(['sellers' => []]);
        }

        $sellers = $em->getRepository(User::class)
            ->createQueryBuilder('u')
            ->where('u.id != :currentUserId')
            ->andWhere('(u.firstName LIKE :query OR u.lastName LIKE :query OR u.email LIKE :query OR CONCAT(u.firstName, \' \', u.lastName) LIKE :query)')
            ->setParameter('currentUserId', $currentUser->getId())
            ->setParameter('query', '%' . $query . '%')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult();

        $sellersData = [];
        foreach ($sellers as $seller) {
            $sellersData[] = [
                'id' => $seller->getId(),
                'name' => $seller->getFullName(),
                'email' => $seller->getEmail(),
                'firstName' => $seller->getFirstName(),
                'lastName' => $seller->getLastName(),
                'isVerified' => $seller->isVerified(),
                'hasProfilePicture' => $seller->getProfilePicture() !== null
            ];
        }

        return new JsonResponse(['sellers' => $sellersData]);
    }

    #[Route('/messages', name: 'app_messages')]
    public function messages(EntityManagerInterface $em): Response
    {
        $currentUser = $this->getUser();
        if (!$currentUser) {
            throw new AccessDeniedException('يجب تسجيل الدخول أولاً');
        }

        // Get all users for potential messaging
        $allUsers = $em->getRepository(User::class)->findBy([], ['firstName' => 'ASC', 'lastName' => 'ASC']);
        
        // Filter out the current user
        $allUsers = array_filter($allUsers, fn($user) => $user->getId() !== $currentUser->getId());

        // Get recent conversations
        $conversations = $em->getRepository(Message::class)->findRecentConversations($currentUser);

        return $this->render('social/messages.html.twig', [
            'currentUser' => $currentUser,
            'allUsers' => $allUsers,
            'conversations' => $conversations,
        ]);
    }
}

<?php

namespace App\Controller;

use App\Entity\Follow;
use App\Entity\Message;
use App\Entity\User;
use App\Repository\FollowRepository;
use App\Repository\MessageRepository;
use App\Service\NotificationService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class SocialController extends AbstractController
{
    public function __construct(
        private NotificationService $notificationService
    ) {}

    #[Route('/api/follow/{userId}', name: 'api_follow_user', methods: ['POST'])]
    #[Route('/web/follow/{userId}', name: 'web_follow_user', methods: ['POST'])]
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

            // Create follow notification
            $this->notificationService->createFollowNotification($userToFollow, $currentUser);

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
    #[Route('/web/unfollow/{userId}', name: 'web_unfollow_user', methods: ['POST'])]
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
    #[Route('/web/follow-status/{userId}', name: 'web_follow_status', methods: ['GET'])]
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

            // Check if user is trying to follow themselves
            if ($currentUser->getId() === $userId) {
                return new JsonResponse([
                    'isFollowing' => false,
                    'followersCount' => $followRepo->getFollowersCount($user),
                    'followingCount' => $followRepo->getFollowingCount($user),
                    'isOwnProfile' => true,
                    'success' => true
                ]);
            }

            $isFollowing = $followRepo->isFollowing($currentUser, $user);
            $followersCount = $followRepo->getFollowersCount($user);
            $followingCount = $followRepo->getFollowingCount($user);

            return new JsonResponse([
                'isFollowing' => $isFollowing,
                'followersCount' => $followersCount,
                'followingCount' => $followingCount,
                'isOwnProfile' => false,
                'success' => true
            ]);
        } catch (\Exception $e) {
            return new JsonResponse([
                'error' => 'حدث خطأ أثناء جلب حالة المتابعة: ' . $e->getMessage(),
                'debug' => [
                    'userId' => $userId,
                    'currentUserId' => $this->getUser() ? $this->getUser()->getId() : null,
                    'exception' => $e->getMessage()
                ]
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
        $isHTML = $data['isHTML'] ?? false;
        $attachment = $data['attachment'] ?? null;
        $attachmentMimeType = $data['attachmentMimeType'] ?? null;
        $attachmentName = $data['attachmentName'] ?? null;

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
        
        // Store HTML flag if provided
        if ($isHTML) {
            $message->setIsHtml(true);
        }
        
        // Store attachment if provided
        if ($attachment && $attachmentMimeType) {
            $message->setAttachment(base64_decode($attachment));
            $message->setAttachmentMimeType($attachmentMimeType);
            $message->setAttachmentName($attachmentName);
        }

        $em->persist($message);
        $em->flush();

        return new JsonResponse([
            'message' => 'تم إرسال الرسالة بنجاح',
            'messageId' => $message->getId(),
            'createdAt' => $message->getCreatedAt()->format('Y-m-d H:i:s'),
            'isHTML' => $isHTML
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
                'isHtml' => $message->isHtml(),
                'hasAttachment' => $message->getAttachment() !== null,
                'attachmentMimeType' => $message->getAttachmentMimeType(),
                'attachmentName' => $message->getAttachmentName(),
                'seenAt' => $message->getSeenAt() ? $message->getSeenAt()->format('Y-m-d H:i:s') : null,
                'readAt' => $message->getReadAt() ? $message->getReadAt()->format('Y-m-d H:i:s') : null,
                'createdAt' => $message->getCreatedAt()->format('Y-m-d H:i:s')
            ];
        }

        return new JsonResponse([
            'messages' => $messagesData,
            'otherUser' => [
                'id' => $otherUser->getId(),
                'name' => $otherUser->getFullName(),
                'email' => $otherUser->getEmail(),
                'isOnline' => $otherUser->isOnline(),
                'lastSeenAt' => $otherUser->getLastSeenAt() ? $otherUser->getLastSeenAt()->format('Y-m-d H:i:s') : null
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



    #[Route('/api/messages/unread-count', name: 'api_messages_unread_count', methods: ['GET'])]
    public function getUnreadMessagesCount(MessageRepository $messageRepo): JsonResponse
    {
        $currentUser = $this->getUser();
        if (!$currentUser) {
            // Return 0 for non-authenticated users
            return new JsonResponse(['count' => 0]);
        }

        $unreadCount = $messageRepo->getUnreadMessagesCount($currentUser);

        return new JsonResponse(['count' => $unreadCount]);
    }

    #[Route('/web/send-message', name: 'web_send_message', methods: ['POST'])]
    public function webSendMessage(Request $request, EntityManagerInterface $em): JsonResponse
    {
        $currentUser = $this->getUser();
        if (!$currentUser) {
            return new JsonResponse(['error' => 'يجب تسجيل الدخول أولاً'], 401);
        }

        $data = json_decode($request->getContent(), true);
        $receiverId = $data['receiverId'] ?? null;
        $content = $data['content'] ?? null;
        $isHTML = $data['isHTML'] ?? false;
        $attachment = $data['attachment'] ?? null;
        $attachmentMimeType = $data['attachmentMimeType'] ?? null;
        $attachmentName = $data['attachmentName'] ?? null;
        $isVoice = $data['isVoice'] ?? false;
        $voiceDuration = $data['voiceDuration'] ?? null;
        $isVideoCall = $data['isVideoCall'] ?? false;
        $callId = $data['callId'] ?? null;

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
        
        // Store HTML flag if provided
        if ($isHTML) {
            $message->setIsHtml(true);
        }
        
        // Store attachment if provided
        if ($attachment && $attachmentMimeType) {
            $message->setAttachment(base64_decode($attachment));
            $message->setAttachmentMimeType($attachmentMimeType);
            $message->setAttachmentName($attachmentName);
        }

        // Store voice message info
        if ($isVoice) {
            $message->setIsVoice(true);
            if ($voiceDuration) {
                $message->setVoiceDuration($voiceDuration);
            }
        }

        // Store video call info
        if ($isVideoCall && $callId) {
            $message->setIsVideoCall(true);
            $message->setCallId($callId);
        }

        // Mark as delivered immediately upon creation
        $message->setIsDelivered(true);

        $em->persist($message);
        $em->flush();

        // Create notification for new message
        $this->notificationService->createMessageNotification($receiver, $currentUser, $message);

        return new JsonResponse([
            'message' => 'تم إرسال الرسالة بنجاح',
            'messageId' => $message->getId(),
            'createdAt' => $message->getCreatedAt()->format('Y-m-d H:i:s'),
            'isHTML' => $isHTML,
            'isDelivered' => $message->isDelivered(),
            'isRead' => $message->isRead()
        ]);
    }

    #[Route('/web/conversation/{userId}', name: 'web_get_conversation', methods: ['GET'])]
    public function webGetConversation(int $userId, EntityManagerInterface $em, MessageRepository $messageRepo): JsonResponse
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
        
        // Mark messages as read and seen
        $now = new \DateTime('now', new \DateTimeZone('Asia/Baghdad'));
        foreach ($messages as $message) {
            if ($message->getReceiver()->getId() === $currentUser->getId()) {
                if (!$message->getSeenAt()) {
                    $message->setSeenAt($now);
                }
                if (!$message->isRead()) {
                    $message->setIsRead(true);
                    $message->setReadAt($now);
                }
            }
        }
        $em->flush();

        $messagesData = [];
        foreach ($messages as $message) {
            $messagesData[] = [
                'id' => $message->getId(),
                'content' => $message->getContent(),
                'senderId' => $message->getSender()->getId(),
                'senderName' => $message->getSender()->getFullName(),
                'receiverId' => $message->getReceiver()->getId(),
                'receiverName' => $message->getReceiver()->getFullName(),
                'isRead' => $message->isRead(),
                'isHtml' => $message->isHtml(),
                'isDelivered' => $message->isDelivered(),
                'hasAttachment' => $message->getAttachment() !== null,
                'attachmentMimeType' => $message->getAttachmentMimeType(),
                'attachmentName' => $message->getAttachmentName(),
                'isVoice' => $message->getIsVoice(),
                'voiceDuration' => $message->getVoiceDuration(),
                'isVideoCall' => $message->getIsVideoCall(),
                'callId' => $message->getCallId(),
                'seenAt' => $message->getSeenAt() ? $message->getSeenAt()->format('Y-m-d H:i:s') : null,
                'readAt' => $message->getReadAt() ? $message->getReadAt()->format('Y-m-d H:i:s') : null,
                'createdAt' => $message->getCreatedAt()->format('Y-m-d H:i:s')
            ];
        }

        return new JsonResponse([
            'messages' => $messagesData,
            'otherUser' => [
                'id' => $otherUser->getId(),
                'name' => $otherUser->getFullName(),
                'email' => $otherUser->getEmail(),
                'isOnline' => $otherUser->isOnline(),
                'lastSeenAt' => $otherUser->getLastSeenAt() ? $otherUser->getLastSeenAt()->format('Y-m-d H:i:s') : null
            ]
        ]);
    }

    #[Route('/web/conversation/{userId}/mark-read', name: 'web_mark_conversation_read', methods: ['POST'])]
    public function webMarkConversationRead(int $userId, EntityManagerInterface $em, MessageRepository $messageRepo): JsonResponse
    {
        $currentUser = $this->getUser();
        if (!$currentUser) {
            return new JsonResponse(['error' => 'يجب تسجيل الدخول أولاً'], 401);
        }

        $otherUser = $em->getRepository(User::class)->find($userId);
        if (!$otherUser) {
            return new JsonResponse(['error' => 'المستخدم غير موجود'], 404);
        }

        $messageRepo->markMessagesAsRead($currentUser, $otherUser);

        return new JsonResponse(['message' => 'تم تمييز الرسائل كمقروءة']);
    }

    #[Route('/web/conversation/{userId}/mark-seen', name: 'web_mark_conversation_seen', methods: ['POST'])]
    public function webMarkConversationSeen(int $userId, EntityManagerInterface $em, MessageRepository $messageRepo): JsonResponse
    {
        $currentUser = $this->getUser();
        if (!$currentUser) {
            return new JsonResponse(['error' => 'يجب تسجيل الدخول أولاً'], 401);
        }

        $otherUser = $em->getRepository(User::class)->find($userId);
        if (!$otherUser) {
            return new JsonResponse(['error' => 'المستخدم غير موجود'], 404);
        }

        // Mark all messages from the other user to current user as seen
        $messages = $em->getRepository(Message::class)->findBy([
            'sender' => $otherUser,
            'receiver' => $currentUser
        ]);

        $now = new \DateTime('now', new \DateTimeZone('Asia/Baghdad'));
        foreach ($messages as $message) {
            if (!$message->getSeenAt()) {
                $message->setSeenAt($now);
            }
        }

        $em->flush();

        return new JsonResponse(['message' => 'تم تمييز الرسائل كمشاهدة']);
    }

    #[Route('/web/conversations', name: 'web_get_conversations', methods: ['GET'])]
    public function webGetConversations(MessageRepository $messageRepo, EntityManagerInterface $em): JsonResponse
    {
        $currentUser = $this->getUser();
        if (!$currentUser) {
            return new JsonResponse(['error' => 'يجب تسجيل الدخول أولاً'], 401);
        }

        $conversations = $messageRepo->findRecentConversations($currentUser);
        $unreadCount = $messageRepo->getUnreadMessagesCount($currentUser);

        // Format conversations for frontend
        $formattedConversations = [];
        foreach ($conversations as $conversation) {
            $otherUser = $em->getRepository(User::class)->find($conversation['otherUserId']);
            if (!$otherUser) {
                continue;
            }

            $formattedConversations[] = [
                'user' => [
                    'id' => $otherUser->getId(),
                    'firstName' => $otherUser->getFirstName(),
                    'lastName' => $otherUser->getLastName(),
                    'fullName' => $otherUser->getFullName(),
                    'email' => $otherUser->getEmail(),
                    'isOnline' => $otherUser->isOnline(),
                    'lastSeenAt' => $otherUser->getLastSeenAt() ? $otherUser->getLastSeenAt()->format('Y-m-d H:i:s') : null
                ],
                'lastMessage' => [
                    'content' => $conversation['lastMessageContent'],
                    'createdAt' => $conversation['lastMessageSentAt']->format('Y-m-d H:i:s')
                ],
                'unreadCount' => $conversation['unreadCount']
            ];
        }

        return new JsonResponse([
            'conversations' => $formattedConversations,
            'unreadCount' => $unreadCount
        ]);
    }

    #[Route('/web/notifications/unread-count', name: 'web_notifications_unread_count', methods: ['GET'])]
    public function getUnreadNotificationsCount(EntityManagerInterface $em): JsonResponse
    {
        $currentUser = $this->getUser();
        if (!$currentUser) {
            // Return 0 for non-authenticated users
            return new JsonResponse(['count' => 0]);
        }

        // Get actual notifications count from Notification entity
        $notificationRepo = $em->getRepository(\App\Entity\Notification::class);
        $unreadCount = $notificationRepo->getUnreadCount($currentUser);

        return new JsonResponse(['count' => $unreadCount]);
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

    #[Route('/api/messages/stream', name: 'api_messages_stream', methods: ['GET'])]
    public function streamMessages(): Response
    {
        $currentUser = $this->getUser();
        if (!$currentUser) {
            return new Response('Unauthorized', 401);
        }

        $response = new Response();
        $response->headers->set('Content-Type', 'text/event-stream');
        $response->headers->set('Cache-Control', 'no-cache');
        $response->headers->set('Connection', 'keep-alive');
        $response->headers->set('Access-Control-Allow-Origin', '*');
        $response->headers->set('Access-Control-Allow-Headers', 'Cache-Control');

        // Send initial connection event
        $response->setContent("data: " . json_encode([
            'type' => 'connected',
            'userId' => $currentUser->getId(),
            'timestamp' => time()
        ]) . "\n\n");

        return $response;
    }

    #[Route('/web/users/{userId}', name: 'web_get_user', methods: ['GET'], requirements: ['userId' => '\d+'])]
    public function getUserById(int $userId, EntityManagerInterface $em): JsonResponse
    {
        $currentUser = $this->getUser();
        if (!$currentUser) {
            return new JsonResponse(['error' => 'يجب تسجيل الدخول أولاً'], 401);
        }

        $user = $em->getRepository(User::class)->find($userId);
        if (!$user) {
            return new JsonResponse(['error' => 'المستخدم غير موجود'], 404);
        }

        return new JsonResponse([
            'user' => [
                'id' => $user->getId(),
                'firstName' => $user->getFirstName(),
                'lastName' => $user->getLastName(),
                'email' => $user->getEmail(),
                'isOnline' => $user->isOnline(),
                'lastSeenAt' => $user->getLastSeenAt() ? $user->getLastSeenAt()->format('Y-m-d H:i:s') : null
            ]
        ]);
    }

    #[Route('/api/users/search', name: 'api_users_search', methods: ['GET'])]
    #[Route('/web/users/search', name: 'web_users_search', methods: ['GET'])]
    public function searchUsers(Request $request, EntityManagerInterface $em): JsonResponse
    {
        $search = $request->query->get('search');
        $location = $request->query->get('location');
        $age = $request->query->get('age');
        $gender = $request->query->get('gender');
        $page = (int) $request->query->get('page', 1);
        $limit = (int) $request->query->get('limit', 10);

        $qb = $em->createQueryBuilder()
            ->select('u')
            ->from(User::class, 'u')
            ->orderBy('u.firstName', 'ASC');

        if ($search) {
            $qb->andWhere('(u.firstName LIKE :search OR u.lastName LIKE :search OR u.email LIKE :search)')
               ->setParameter('search', '%' . $search . '%');
        }
        
        if ($location) {
            $qb->andWhere('u.location LIKE :location')
               ->setParameter('location', '%' . $location . '%');
        }
        
        if ($gender) {
            $qb->andWhere('u.gender = :gender')
               ->setParameter('gender', $gender);
        }
        
        if ($age) {
            switch ($age) {
                case '18-25':
                    $qb->andWhere('u.age >= 18 AND u.age <= 25');
                    break;
                case '26-35':
                    $qb->andWhere('u.age >= 26 AND u.age <= 35');
                    break;
                case '36-45':
                    $qb->andWhere('u.age >= 36 AND u.age <= 45');
                    break;
                case '46-55':
                    $qb->andWhere('u.age >= 46 AND u.age <= 55');
                    break;
                case '55+':
                    $qb->andWhere('u.age >= 55');
                    break;
            }
        }

        $totalQuery = clone $qb;
        $total = $totalQuery->select('COUNT(u.id)')->getQuery()->getSingleScalarResult();

        $users = $qb->setFirstResult(($page - 1) * $limit)
                   ->setMaxResults($limit)
                   ->getQuery()
                   ->getResult();

        $usersData = [];
        foreach ($users as $user) {
            // Get user avatar URL
            $avatarUrl = $user->getAvatarUrl();
                
            $usersData[] = [
                'id' => $user->getId(),
                'name' => $user->getFullName(),
                'firstName' => $user->getFirstName(),
                'lastName' => $user->getLastName(),
                'email' => $user->getEmail(),
                'age' => $user->getAge(),
                'location' => $user->getLocation(),
                'gender' => $user->getGender(),
                'isVerified' => $user->isVerified(),
                'hasProfileImage' => $user->getProfilePicture() !== null,
                'avatar' => $avatarUrl,
                'profileImage' => $avatarUrl
            ];
        }

        return new JsonResponse([
            'users' => $usersData,
            'pagination' => [
                'page' => $page,
                'limit' => $limit,
                'total' => $total,
                'pages' => ceil($total / $limit)
            ]
        ]);
    }

    #[Route('/api/user/avatar/{id}', name: 'api_user_avatar', methods: ['GET'])]
    public function getUserAvatar(int $id, EntityManagerInterface $em): Response
    {
        $user = $em->getRepository(User::class)->find($id);
        
        if (!$user || !$user->getProfilePicture() || !$user->getProfilePictureMimeType()) {
            // Return default avatar
            $defaultAvatarPath = __DIR__ . '/../../public/images/default-avatar.png';
            if (!file_exists($defaultAvatarPath)) {
                // Create a simple default avatar if it doesn't exist
                $this->createDefaultAvatar($defaultAvatarPath);
            }
            
            return new Response(
                file_get_contents($defaultAvatarPath),
                200,
                ['Content-Type' => 'image/png']
            );
        }

        $avatarData = stream_get_contents($user->getProfilePicture());
        
        return new Response(
            $avatarData,
            200,
            ['Content-Type' => $user->getProfilePictureMimeType()]
        );
    }

    #[Route('/web/message/{messageId}/attachment', name: 'web_message_attachment', methods: ['GET'])]
    public function getMessageAttachment(int $messageId, EntityManagerInterface $em): Response
    {
        $currentUser = $this->getUser();
        if (!$currentUser) {
            return new Response('Unauthorized', 401);
        }

        $message = $em->getRepository(Message::class)->find($messageId);
        if (!$message || !$message->getAttachment()) {
            return new Response('Attachment not found', 404);
        }

        // Check if user is sender or receiver
        if ($message->getSender()->getId() !== $currentUser->getId() && 
            $message->getReceiver()->getId() !== $currentUser->getId()) {
            return new Response('Access denied', 403);
        }

        $attachmentData = stream_get_contents($message->getAttachment());
        
        return new Response(
            $attachmentData,
            200,
            [
                'Content-Type' => $message->getAttachmentMimeType(),
                'Content-Disposition' => 'inline; filename="' . $message->getAttachmentName() . '"'
            ]
        );
    }

    #[Route('/web/message/{messageId}/mark-seen', name: 'web_mark_seen', methods: ['POST'])]
    public function markMessageAsSeen(int $messageId, EntityManagerInterface $em): JsonResponse
    {
        $currentUser = $this->getUser();
        if (!$currentUser) {
            return new JsonResponse(['error' => 'يجب تسجيل الدخول أولاً'], 401);
        }

        $message = $em->getRepository(Message::class)->find($messageId);
        if (!$message) {
            return new JsonResponse(['error' => 'الرسالة غير موجودة'], 404);
        }

        // Check if user is the receiver
        if ($message->getReceiver()->getId() !== $currentUser->getId()) {
            return new JsonResponse(['error' => 'غير مسموح'], 403);
        }

        $message->setSeenAt(new \DateTime());
        $em->flush();

        return new JsonResponse(['message' => 'تم تسجيل رؤية الرسالة']);
    }

    #[Route('/web/user/{userId}/status', name: 'web_user_status', methods: ['GET'])]
    public function getUserStatus(int $userId, EntityManagerInterface $em): JsonResponse
    {
        $currentUser = $this->getUser();
        if (!$currentUser) {
            return new JsonResponse(['error' => 'يجب تسجيل الدخول أولاً'], 401);
        }

        $user = $em->getRepository(User::class)->find($userId);
        if (!$user) {
            return new JsonResponse(['error' => 'المستخدم غير موجود'], 404);
        }

        return new JsonResponse([
            'isOnline' => $user->isOnline(),
            'lastSeenAt' => $user->getLastSeenAt() ? $user->getLastSeenAt()->format('Y-m-d H:i:s') : null
        ]);
    }

    #[Route('/web/user/update-status', name: 'web_update_status', methods: ['POST'])]
    public function updateUserStatus(Request $request, EntityManagerInterface $em): JsonResponse
    {
        $currentUser = $this->getUser();
        if (!$currentUser) {
            return new JsonResponse(['error' => 'يجب تسجيل الدخول أولاً'], 401);
        }

        $data = json_decode($request->getContent(), true);
        $isOnline = $data['isOnline'] ?? false;

        $currentUser->setIsOnline($isOnline);
        if ($isOnline) {
            $currentUser->setLastSeenAt(new \DateTime('now', new \DateTimeZone('Asia/Baghdad')));
        }
        
        $em->flush();

        return new JsonResponse(['message' => 'تم تحديث الحالة']);
    }

    private function createDefaultAvatar(string $path): void
    {
        // Create directory if it doesn't exist
        $dir = dirname($path);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        // Create a simple default avatar (32x32 PNG with user icon)
        $image = imagecreate(32, 32);
        $bgColor = imagecolorallocate($image, 200, 200, 200);
        $textColor = imagecolorallocate($image, 100, 100, 100);
        
        // Draw a simple user icon
        imagefilledellipse($image, 16, 12, 16, 16, $textColor);
        imagefilledellipse($image, 16, 26, 12, 8, $textColor);
        
        imagepng($image, $path);
        imagedestroy($image);
    }

    #[Route('/users', name: 'app_users')]
    public function users(EntityManagerInterface $em): Response
    {
        $currentUser = $this->getUser();
        if (!$currentUser) {
            throw new AccessDeniedException('يجب تسجيل الدخول أولاً');
        }

        // Get top sellers in Iraq based on followers count
        $topSellers = $em->getRepository(User::class)->createQueryBuilder('u')
            ->select('u', 'COUNT(f.id) as HIDDEN followersCount')
            ->leftJoin('App\Entity\Follow', 'f', 'WITH', 'f.following = u.id')
            ->where('u.id != :currentUserId')
            ->setParameter('currentUserId', $currentUser->getId())
            ->groupBy('u.id')
            ->orderBy('followersCount', 'DESC')
            ->setMaxResults(6)
            ->getQuery()
            ->getResult();

        return $this->render('social/users.html.twig', [
            'users' => [],  // Empty array - don't show all users
            'topSellers' => $topSellers,
            'currentUser' => $currentUser,
            'user' => $currentUser
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
    #[Route('/web/search-sellers', name: 'web_search_sellers', methods: ['GET'])]
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
    public function messages(EntityManagerInterface $em, Request $request): Response
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

        // Handle product data from query parameters
        $sellerId = $request->query->get('seller');
        $productData = $request->query->get('product');
        $product = null;
        $seller = null;

        if ($sellerId) {
            $seller = $em->getRepository(User::class)->find($sellerId);
        }

        if ($productData) {
            $product = json_decode($productData, true);
        }

        return $this->render('social/messages.html.twig', [
            'currentUser' => $currentUser,
            'allUsers' => $allUsers,
            'conversations' => $conversations,
            'seller' => $seller,
            'product' => $product,
        ]);
    }

    #[Route('/web/notifications', name: 'web_notifications', methods: ['GET'])]
    public function notifications(): Response
    {
        $currentUser = $this->getUser();
        if (!$currentUser) {
            return $this->redirectToRoute('app_login');
        }

        return $this->render('notifications/index.html.twig', [
            'currentUser' => $currentUser,
        ]);
    }


    #[Route('/web/notifications/list', name: 'web_notifications_list', methods: ['GET'])]
    public function getNotifications(EntityManagerInterface $em, Request $request): JsonResponse
    {
        $currentUser = $this->getUser();
        if (!$currentUser) {
            return new JsonResponse(['error' => 'يجب تسجيل الدخول أولاً'], 401);
        }

        $page = (int) $request->query->get('page', 1);
        $limit = (int) $request->query->get('limit', 20);
        $offset = ($page - 1) * $limit;

        $notificationRepo = $em->getRepository(\App\Entity\Notification::class);
        $notifications = $notificationRepo->getNotifications($currentUser, $limit, $offset);

        $notificationsData = [];
        foreach ($notifications as $notification) {
            $notificationsData[] = [
                'id' => $notification->getId(),
                'title' => $notification->getTitle(),
                'message' => $notification->getMessage(),
                'type' => $notification->getType(),
                'isRead' => $notification->isRead(),
                'createdAt' => $notification->getCreatedAt()->format('Y-m-d H:i:s'),
                'readAt' => $notification->getReadAt() ? $notification->getReadAt()->format('Y-m-d H:i:s') : null,
                'productId' => $notification->getProduct() ? $notification->getProduct()->getId() : null,
                'productTitle' => $notification->getProduct() ? $notification->getProduct()->getTitle() : null,
                'sellerId' => $notification->getSeller() ? $notification->getSeller()->getId() : null,
                'sellerName' => $notification->getSeller() ? $notification->getSeller()->getFullName() : null,
            ];
        }

        return new JsonResponse(['notifications' => $notificationsData]);
    }

    #[Route('/web/notifications/{id}/mark-read', name: 'web_notification_mark_read', methods: ['POST'])]
    public function markNotificationAsRead(int $id, EntityManagerInterface $em): JsonResponse
    {
        $currentUser = $this->getUser();
        if (!$currentUser) {
            return new JsonResponse(['error' => 'يجب تسجيل الدخول أولاً'], 401);
        }

        $notificationRepo = $em->getRepository(\App\Entity\Notification::class);
        $success = $notificationRepo->markAsRead($currentUser, $id);

        if ($success) {
            return new JsonResponse(['message' => 'تم تمييز الإشعار كمقروء']);
        } else {
            return new JsonResponse(['error' => 'الإشعار غير موجود'], 404);
        }
    }

    #[Route('/web/notifications/mark-all-read', name: 'web_notifications_mark_all_read', methods: ['POST'])]
    public function markAllNotificationsAsRead(EntityManagerInterface $em): JsonResponse
    {
        $currentUser = $this->getUser();
        if (!$currentUser) {
            return new JsonResponse(['error' => 'يجب تسجيل الدخول أولاً'], 401);
        }

        $notificationRepo = $em->getRepository(\App\Entity\Notification::class);
        $count = $notificationRepo->markAllAsRead($currentUser);

        return new JsonResponse(['message' => "تم تمييز {$count} إشعار كمقروء"]);
    }
}

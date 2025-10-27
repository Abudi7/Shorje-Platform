<?php

namespace App\Repository;

use App\Entity\Message;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Message>
 */
class MessageRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Message::class);
    }

    public function findConversation(User $user1, User $user2): array
    {
        return $this->createQueryBuilder('m')
            ->where('(m.sender = :user1 AND m.receiver = :user2) OR (m.sender = :user2 AND m.receiver = :user1)')
            ->setParameter('user1', $user1)
            ->setParameter('user2', $user2)
            ->orderBy('m.createdAt', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function getUnreadMessagesCount(User $user): int
    {
        return $this->createQueryBuilder('m')
            ->select('COUNT(m.id)')
            ->where('m.receiver = :user')
            ->andWhere('m.isRead = false')
            ->setParameter('user', $user)
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function markAsRead(User $sender, User $receiver): void
    {
        $this->createQueryBuilder('m')
            ->update()
            ->set('m.isRead', true)
            ->where('m.sender = :sender')
            ->andWhere('m.receiver = :receiver')
            ->andWhere('m.isRead = false')
            ->setParameter('sender', $sender)
            ->setParameter('receiver', $receiver)
            ->getQuery()
            ->execute();
    }

    public function findRecentConversations(User $user): array
    {
        // Get all messages where user is either sender or receiver
        $messages = $this->createQueryBuilder('m')
            ->join('m.sender', 's')
            ->join('m.receiver', 'r')
            ->where('s.id = :userId OR r.id = :userId')
            ->setParameter('userId', $user->getId())
            ->orderBy('m.createdAt', 'DESC')
            ->getQuery()
            ->getResult();

        // Group by conversation partner and get latest message
        $groupedConversations = [];
        foreach ($messages as $message) {
            $sender = $message->getSender();
            $receiver = $message->getReceiver();
            
            // Determine the other user in the conversation
            $otherUser = ($sender->getId() === $user->getId()) ? $receiver : $sender;
            $otherUserId = $otherUser->getId();
            
            // Only keep the latest message for each conversation
            if (!isset($groupedConversations[$otherUserId])) {
                // Calculate unread count for this conversation
                $unreadCount = $this->countUnreadMessages($user, $otherUser);
                
                $groupedConversations[$otherUserId] = [
                    'otherUserId' => $otherUserId,
                    'otherUserName' => $otherUser->getFullName(),
                    'lastMessageContent' => $message->getContent(),
                    'lastMessageSentAt' => $message->getCreatedAt(),
                    'unreadCount' => $unreadCount
                ];
            }
        }
        
        return array_values($groupedConversations);
    }

    public function countUnreadMessages(User $currentUser, User $otherUser): int
    {
        return $this->createQueryBuilder('m')
            ->select('COUNT(m.id)')
            ->where('m.sender = :otherUser')
            ->andWhere('m.receiver = :currentUser')
            ->andWhere('m.isRead = false')
            ->setParameter('otherUser', $otherUser)
            ->setParameter('currentUser', $currentUser)
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function markMessagesAsRead(User $currentUser, User $otherUser): void
    {
        $this->createQueryBuilder('m')
            ->update()
            ->set('m.isRead', true)
            ->where('m.sender = :otherUser')
            ->andWhere('m.receiver = :currentUser')
            ->andWhere('m.isRead = false')
            ->setParameter('otherUser', $otherUser)
            ->setParameter('currentUser', $currentUser)
            ->getQuery()
            ->execute();
    }
}

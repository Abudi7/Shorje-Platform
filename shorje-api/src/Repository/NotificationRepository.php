<?php

namespace App\Repository;

use App\Entity\Notification;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Notification>
 */
class NotificationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Notification::class);
    }

    /**
     * Get notifications for a specific user
     */
    public function findByUser(User $user, int $limit = 50, int $offset = 0): array
    {
        return $this->createQueryBuilder('n')
            ->andWhere('n.user = :user')
            ->andWhere('n.expiresAt IS NULL OR n.expiresAt > :now')
            ->setParameter('user', $user)
            ->setParameter('now', new \DateTime('now', new \DateTimeZone('Asia/Baghdad')))
            ->orderBy('n.createdAt', 'DESC')
            ->setMaxResults($limit)
            ->setFirstResult($offset)
            ->getQuery()
            ->getResult();
    }

    /**
     * Get unread notifications count for a user
     */
    public function getUnreadCount(User $user): int
    {
        return $this->createQueryBuilder('n')
            ->select('COUNT(n.id)')
            ->andWhere('n.user = :user')
            ->andWhere('n.isRead = false')
            ->andWhere('n.expiresAt IS NULL OR n.expiresAt > :now')
            ->setParameter('user', $user)
            ->setParameter('now', new \DateTime('now', new \DateTimeZone('Asia/Baghdad')))
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * Get unread notifications for a user
     */
    public function getUnreadNotifications(User $user, int $limit = 20): array
    {
        return $this->createQueryBuilder('n')
            ->andWhere('n.user = :user')
            ->andWhere('n.isRead = false')
            ->andWhere('n.expiresAt IS NULL OR n.expiresAt > :now')
            ->setParameter('user', $user)
            ->setParameter('now', new \DateTime('now', new \DateTimeZone('Asia/Baghdad')))
            ->orderBy('n.createdAt', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    /**
     * Get notifications by type for a user
     */
    public function findByUserAndType(User $user, string $type, int $limit = 20): array
    {
        return $this->createQueryBuilder('n')
            ->andWhere('n.user = :user')
            ->andWhere('n.type = :type')
            ->andWhere('n.expiresAt IS NULL OR n.expiresAt > :now')
            ->setParameter('user', $user)
            ->setParameter('type', $type)
            ->setParameter('now', new \DateTime('now', new \DateTimeZone('Asia/Baghdad')))
            ->orderBy('n.createdAt', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    /**
     * Mark all notifications as read for a user
     */
    public function markAllAsRead(User $user): int
    {
        return $this->createQueryBuilder('n')
            ->update()
            ->set('n.isRead', true)
            ->set('n.readAt', ':now')
            ->andWhere('n.user = :user')
            ->andWhere('n.isRead = false')
            ->setParameter('user', $user)
            ->setParameter('now', new \DateTime('now', new \DateTimeZone('Asia/Baghdad')))
            ->getQuery()
            ->execute();
    }

    /**
     * Mark notifications as read by IDs
     */
    public function markAsReadByIds(array $ids, User $user): int
    {
        if (empty($ids)) {
            return 0;
        }

        return $this->createQueryBuilder('n')
            ->update()
            ->set('n.isRead', true)
            ->set('n.readAt', ':now')
            ->andWhere('n.id IN (:ids)')
            ->andWhere('n.user = :user')
            ->andWhere('n.isRead = false')
            ->setParameter('ids', $ids)
            ->setParameter('user', $user)
            ->setParameter('now', new \DateTime('now', new \DateTimeZone('Asia/Baghdad')))
            ->getQuery()
            ->execute();
    }

    /**
     * Get important notifications for a user
     */
    public function getImportantNotifications(User $user, int $limit = 10): array
    {
        return $this->createQueryBuilder('n')
            ->andWhere('n.user = :user')
            ->andWhere('n.isImportant = true')
            ->andWhere('n.expiresAt IS NULL OR n.expiresAt > :now')
            ->setParameter('user', $user)
            ->setParameter('now', new \DateTime('now', new \DateTimeZone('Asia/Baghdad')))
            ->orderBy('n.createdAt', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    /**
     * Clean up expired notifications
     */
    public function cleanupExpired(): int
    {
        return $this->createQueryBuilder('n')
            ->delete()
            ->andWhere('n.expiresAt IS NOT NULL')
            ->andWhere('n.expiresAt < :now')
            ->setParameter('now', new \DateTime('now', new \DateTimeZone('Asia/Baghdad')))
            ->getQuery()
            ->execute();
    }

    /**
     * Get notification statistics for a user
     */
    public function getNotificationStats(User $user): array
    {
        $qb = $this->createQueryBuilder('n')
            ->select([
                'COUNT(n.id) as total',
                'SUM(CASE WHEN n.isRead = false THEN 1 ELSE 0 END) as unread',
                'SUM(CASE WHEN n.isImportant = true AND n.isRead = false THEN 1 ELSE 0 END) as important_unread'
            ])
            ->andWhere('n.user = :user')
            ->andWhere('n.expiresAt IS NULL OR n.expiresAt > :now')
            ->setParameter('user', $user)
            ->setParameter('now', new \DateTime('now', new \DateTimeZone('Asia/Baghdad')));

        $result = $qb->getQuery()->getSingleResult();

        return [
            'total' => (int) $result['total'],
            'unread' => (int) $result['unread'],
            'important_unread' => (int) $result['important_unread']
        ];
    }
}
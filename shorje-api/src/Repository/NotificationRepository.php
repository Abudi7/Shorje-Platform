<?php

namespace App\Repository;

use App\Entity\Notification;
use App\Entity\User;
use App\Entity\Product;
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

    public function getUnreadCount(User $user): int
    {
        return $this->createQueryBuilder('n')
            ->select('COUNT(n.id)')
            ->where('n.user = :user')
            ->andWhere('n.isRead = false')
            ->setParameter('user', $user)
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function getNotifications(User $user, int $limit = 20, int $offset = 0): array
    {
        return $this->createQueryBuilder('n')
            ->where('n.user = :user')
            ->setParameter('user', $user)
            ->orderBy('n.createdAt', 'DESC')
            ->setMaxResults($limit)
            ->setFirstResult($offset)
            ->getQuery()
            ->getResult();
    }

    public function markAsRead(User $user, int $notificationId): bool
    {
        $notification = $this->findOneBy([
            'id' => $notificationId,
            'user' => $user
        ]);

        if ($notification) {
            $notification->setIsRead(true);
            $this->getEntityManager()->flush();
            return true;
        }

        return false;
    }

    public function markAllAsRead(User $user): int
    {
        return $this->createQueryBuilder('n')
            ->update()
            ->set('n.isRead', true)
            ->set('n.readAt', ':now')
            ->where('n.user = :user')
            ->andWhere('n.isRead = false')
            ->setParameter('user', $user)
            ->setParameter('now', new \DateTime())
            ->getQuery()
            ->execute();
    }

    public function createProductNotification(User $user, User $seller, Product $product): Notification
    {
        $notification = new Notification();
        $notification->setUser($user);
        $notification->setSeller($seller);
        $notification->setProduct($product);
        $notification->setType('new_product');
        $notification->setTitle('منتج جديد من ' . $seller->getFullName());
        $notification->setMessage($seller->getFullName() . ' نشر منتج جديد: ' . $product->getTitle());
        
        $this->getEntityManager()->persist($notification);
        $this->getEntityManager()->flush();

        return $notification;
    }
}

<?php

namespace App\Repository;

use App\Entity\ProductFavorite;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ProductFavorite>
 */
class ProductFavoriteRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ProductFavorite::class);
    }

    /**
     * Get all favorite products for a user
     */
    public function findByUser(User $user): array
    {
        return $this->createQueryBuilder('pf')
            ->andWhere('pf.user = :user')
            ->setParameter('user', $user)
            ->orderBy('pf.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Check if product is favorited by user
     */
    public function isFavorited(User $user, int $productId): bool
    {
        $count = $this->createQueryBuilder('pf')
            ->select('COUNT(pf.id)')
            ->andWhere('pf.user = :user')
            ->andWhere('pf.product = :productId')
            ->setParameter('user', $user)
            ->setParameter('productId', $productId)
            ->getQuery()
            ->getSingleScalarResult();

        return $count > 0;
    }

    /**
     * Find favorite by user and product
     */
    public function findByUserAndProduct(User $user, int $productId): ?ProductFavorite
    {
        return $this->createQueryBuilder('pf')
            ->andWhere('pf.user = :user')
            ->andWhere('pf.product = :productId')
            ->setParameter('user', $user)
            ->setParameter('productId', $productId)
            ->getQuery()
            ->getOneOrNullResult();
    }
}


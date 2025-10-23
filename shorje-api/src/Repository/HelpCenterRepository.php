<?php

namespace App\Repository;

use App\Entity\HelpCenter;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<HelpCenter>
 */
class HelpCenterRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, HelpCenter::class);
    }

    public function findActive(): array
    {
        return $this->createQueryBuilder('h')
            ->where('h.isActive = :active')
            ->setParameter('active', true)
            ->orderBy('h.sortOrder', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findByCategory(string $category): array
    {
        return $this->createQueryBuilder('h')
            ->where('h.isActive = :active')
            ->andWhere('h.category = :category')
            ->setParameter('active', true)
            ->setParameter('category', $category)
            ->orderBy('h.sortOrder', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function search(string $query): array
    {
        return $this->createQueryBuilder('h')
            ->where('h.isActive = :active')
            ->andWhere('h.title LIKE :query OR h.content LIKE :query')
            ->setParameter('active', true)
            ->setParameter('query', '%' . $query . '%')
            ->orderBy('h.sortOrder', 'ASC')
            ->getQuery()
            ->getResult();
    }
}

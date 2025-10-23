<?php

namespace App\Repository;

use App\Entity\TermsAndConditions;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<TermsAndConditions>
 */
class TermsAndConditionsRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, TermsAndConditions::class);
    }

    public function findCurrent(): ?TermsAndConditions
    {
        return $this->createQueryBuilder('t')
            ->where('t.isActive = :active')
            ->andWhere('t.isCurrent = :current')
            ->setParameter('active', true)
            ->setParameter('current', true)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function findActive(): array
    {
        return $this->createQueryBuilder('t')
            ->where('t.isActive = :active')
            ->setParameter('active', true)
            ->orderBy('t.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }
}

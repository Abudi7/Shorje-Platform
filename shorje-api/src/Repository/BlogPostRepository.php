<?php

namespace App\Repository;

use App\Entity\BlogPost;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<BlogPost>
 */
class BlogPostRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, BlogPost::class);
    }

    public function findPublished(): array
    {
        return $this->createQueryBuilder('b')
            ->where('b.isPublished = :published')
            ->setParameter('published', true)
            ->orderBy('b.publishedAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function findByCategory(string $category): array
    {
        return $this->createQueryBuilder('b')
            ->where('b.isPublished = :published')
            ->andWhere('b.category = :category')
            ->setParameter('published', true)
            ->setParameter('category', $category)
            ->orderBy('b.publishedAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function findPopular(int $limit = 10): array
    {
        return $this->createQueryBuilder('b')
            ->where('b.isPublished = :published')
            ->setParameter('published', true)
            ->orderBy('b.viewCount', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }
}

<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\WebsiteFeature;
use App\Model\FeaturesStatusEnum;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<WebsiteFeature>
 */
class WebsiteFeatureRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, WebsiteFeature::class);
    }

    /**
     * Retrieves all features ordered by priority and ID, highest first.
     *
     * @return list<WebsiteFeature>
     */
    public function findAllOrderedByPriority(): array
    {
        return $this->createQueryBuilder('f')
            ->orderBy('f.priority', 'DESC')
            ->addOrderBy('f.id', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Retrieves completed features ordered by priority, highest first.
     *
     * @return list<WebsiteFeature>
     */
    public function findCompleted(): array
    {
        return $this->createQueryBuilder('f')
            ->andWhere('f.status = :status')
            ->setParameter('status', FeaturesStatusEnum::COMPLETED_FEATURE)
            ->orderBy('f.priority', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Retrieves unfinished features ordered by priority, highest first.
     *
     * @return list<WebsiteFeature>
     */
    public function findToDo(): array
    {
        return $this->createQueryBuilder('f')
            ->andWhere('f.status != :status')
            ->setParameter('status', FeaturesStatusEnum::COMPLETED_FEATURE)
            ->orderBy('f.priority', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Builds the query for unfinished features without loading all rows.
     */
    public function createToDoQueryBuilder(): \Doctrine\ORM\QueryBuilder
    {
        return $this->createQueryBuilder('f')
            ->andWhere('f.status != :completed')
            ->setParameter('completed', FeaturesStatusEnum::COMPLETED_FEATURE)
            ->orderBy('f.priority', 'DESC')
            ->addOrderBy('f.id', 'DESC');
    }

    /**
     * Builds the query for completed features without loading all rows.
     */
    public function createCompletedQueryBuilder(): \Doctrine\ORM\QueryBuilder
    {
        return $this->createQueryBuilder('f')
            ->andWhere('f.status = :completed')
            ->setParameter('completed', FeaturesStatusEnum::COMPLETED_FEATURE)
            ->orderBy('f.priority', 'DESC')
            ->addOrderBy('f.id', 'DESC');
    }

    /**
     * Finds a single website feature by its ID.
     */
    public function findOne(int $id): ?WebsiteFeature
    {
        return $this->find($id);
    }

    /**
     * Retrieves features matching the given status, ordered by priority
     * and ID in descending order.
     *
     * @return list<WebsiteFeature>
     */
    public function findByStatusOrderedByPriority(
        FeaturesStatusEnum $status
    ): array {
        return $this->createQueryBuilder('f')
            ->andWhere('f.status = :status')
            ->setParameter('status', $status)
            ->orderBy('f.priority', 'DESC')
            ->addOrderBy('f.id', 'DESC')
            ->getQuery()
            ->getResult();
    }
}

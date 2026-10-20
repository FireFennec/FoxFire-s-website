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

    // Retrieve all website features ordered by priority.
    public function findAll(): array
    {
        return $this->createQueryBuilder('f')
            ->orderBy('f.priority', 'DESC')
            ->getQuery()
            ->getResult();
    }

    // Retrieve completed features ordered by priority.
    /**
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

    // Retrieve unfinished features ordered by priority.
    /**
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

    // Find a single website feature by its ID.
    public function findOne(int $id): ?WebsiteFeature
    {
        return $this->find($id);
    }

    /**
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

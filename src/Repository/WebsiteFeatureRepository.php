<?php

namespace App\Repository;

use App\Entity\WebsiteFeature;
use Doctrine\ORM\EntityManagerInterface;

readonly class WebsiteFeatureRepository
{
    // Inject the EntityManager to interact with the database.
    public function __construct(
        private EntityManagerInterface $entityManager
    ) {
    }

    // Retrieve all website features, ordered by priority from highest to lowest.
    public function findAll(): array
    {
        return $this->entityManager->createQueryBuilder()
            ->select('s')
            ->from(WebsiteFeature::class, 's')
            ->orderBy('s.priority', 'DESC')
            ->getQuery()
            ->getResult();
    }

    // Find a single website feature by its ID, or return null if it does not exist.
    public function findOne(int $id): ?WebsiteFeature
    {
        return $this->entityManager->createQueryBuilder()
            ->select('s')
            ->from(WebsiteFeature::class, 's')
            ->where('s.id = :id')
            ->setParameter('id', $id)
            ->getQuery()
            ->getOneOrNullResult();
    }
}

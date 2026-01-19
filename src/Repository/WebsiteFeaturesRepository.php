<?php

namespace App\Repository;

use AllowDynamicProperties;
use App\Entity\WebsiteFeature;
use Doctrine\ORM\EntityManagerInterface;

#[AllowDynamicProperties]
class WebsiteFeaturesRepository
{
    public function __construct(
        EntityManagerInterface $entityManager
    ) {
        $this->entityManager = $entityManager;
    }

    public function findAll(): array
    {
        return $this->entityManager->createQueryBuilder()
            ->select('s')
            ->from(WebsiteFeature::class, 's')
            ->orderBy('s.priority', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function findOne(int $id): ?WebsiteFeature
    {
        foreach ($this->findAll() as $websiteFeature) {
            if ($websiteFeature->getId() === $id) {
                return $websiteFeature;
            }
        }

        return null;
    }
}

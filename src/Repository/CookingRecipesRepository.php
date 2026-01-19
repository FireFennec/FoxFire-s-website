<?php

namespace App\Repository;

use AllowDynamicProperties;
use App\Entity\CookingRecipe;
use Doctrine\ORM\EntityManagerInterface;

#[AllowDynamicProperties]
class CookingRecipesRepository
{
    public function __construct(
        EntityManagerInterface $entityManager,
    ) {
        $this->entityManager = $entityManager;
    }

    public function findAll(): array
    {
        return $this->entityManager->createQueryBuilder()
            ->select('s')
            ->from(CookingRecipe::class, 's')
            ->orderBy('s.id', 'ASC')
            ->getQuery()
            ->getResult();
    }
}

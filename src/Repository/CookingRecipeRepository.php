<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\CookingRecipe;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<CookingRecipe>
 */
class CookingRecipeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CookingRecipe::class);
    }

    // Return all recipes ordered by their ID.
    public function findAllOrderedById(): array
    {
        return $this->createQueryBuilder('recipe')
            ->orderBy('recipe.id', 'ASC')
            ->getQuery()
            ->getResult();
    }
}

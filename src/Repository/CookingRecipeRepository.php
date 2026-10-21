<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\CookingRecipe;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
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

    /**
     * Builds a query for recipes ordered by ID in descending order.
     */
    public function createOrderedQueryBuilder(): QueryBuilder
    {
        return $this->createQueryBuilder('recipe')
            ->orderBy('recipe.id', 'DESC');
    }
}

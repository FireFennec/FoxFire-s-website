<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\PersonalSkill;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\QueryBuilder;

/**
 * @extends ServiceEntityRepository<PersonalSkill>
 */
class PersonalSkillRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PersonalSkill::class);
    }

    /**
     * Retrieves personal skills ordered by proficiency level, highest first.
     *
     * @return list<PersonalSkill>
     */
    public function findAllOrderedByLevel(): array
    {
        return $this->createQueryBuilder('s')
            ->orderBy('s.levelOfSkill', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Creates a query builder for personal skills ordered
     * by proficiency level, highest first.
     */
    public function createOrderedQueryBuilder(): QueryBuilder
    {
        return $this->createQueryBuilder('s')
            ->orderBy('s.levelOfSkill', 'DESC')
            ->addOrderBy('s.id', 'ASC');
    }
}

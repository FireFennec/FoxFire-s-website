<?php

namespace App\Repository;

use AllowDynamicProperties;
use App\Entity\PersonalSkill;
use Doctrine\ORM\EntityManagerInterface;

#[AllowDynamicProperties]
class PersonalSkillsRepository
{
    public function __construct(
        EntityManagerInterface $entityManager
    )
    {
        $this->entityManager = $entityManager;
    }

    public function findAll(): array
    {
        return $this->entityManager->createQueryBuilder()
            ->select('s')
            ->from(PersonalSkill::class, 's')
            ->orderBy('s.levelOfSkill', 'DESC')
            ->getQuery()
            ->getResult();
    }
}

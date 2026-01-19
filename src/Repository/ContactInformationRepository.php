<?php

namespace App\Repository;

use AllowDynamicProperties;
use App\Entity\ContactInformation;
use Doctrine\ORM\EntityManagerInterface;

#[AllowDynamicProperties]
class ContactInformationRepository
{
    public function __construct(
        EntityManagerInterface $entityManager
    ) {
        $this->entityManager = $entityManager;
    }

    public function findOneById(int $id): ?ContactInformation
    {
        foreach ($this->findAll() as $contactInformation)
        {
            if ($contactInformation->getId() == $id) {
                return $contactInformation;
            }
        }
        return null;
    }

    public function findOneByEmail(string $email): ?ContactInformation
    {
        foreach ($this->findAll() as $contactInformation)
        {
            if ($contactInformation->getEmail() == $email) {
                return $contactInformation;
            }
        }
        return null;
    }

    public function findAll(): ?Array
    {
        return $this->entityManager->createQueryBuilder()
            ->select('s')
            ->from(ContactInformation::class, 's')
            ->orderBy('s.id', 'ASC')
            ->getQuery()
            ->getResult();
    }
}

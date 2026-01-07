<?php

namespace App\Repository;

use App\Model\ContactInformations;

class ContactInformationsRepository
{
    public function findOneById(int $id): ?ContactInformations
    {
       foreach ($this->findAll() as $contactInformation)
       {
           if ($contactInformation->getId() == $id) {
               return $contactInformation;
           }
       }
        return null;
    }

    public function findAll(): array
    {
        return [
            new ContactInformations(
                1,
                '+420739950157',
                'DanVraspir@protonmail.com',
                'foxfire7445',
                'FoxFireCZ',
                'FoxFireCZ',
                'https://x.com/FoxFireCZ',
                'FoxFire_AD',
                'https://x.com/FoxFire_ad',
                '@FoxFireCZ',
                'https://barq.app/@FoxFireCZ'
            )
        ];
    }
}

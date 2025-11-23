<?php

namespace App\Repository;

use App\Model\ContactInformations;

class ContactInformationRepository
{
    public function findAll(): array
    {
        return [
            new ContactInformations(
                1,
                '+420739950157',
                'DanVraspir@protonmail.com',
                'foxfire7445',
                '@FoxFireCZ',
                'FoxFireCZ',
                'FoxFire_AD',
                '@FoxFireCZ'
            )
        ];
    }
}

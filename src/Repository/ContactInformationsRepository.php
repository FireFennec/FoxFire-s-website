<?php

namespace App\Repository;

use App\Model\ContactInformations;

class ContactInformationsRepository
{
    public function findOne($email): ?array
    {
        if ($email == 'DanVraspir@protonmail.com') {
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
                '@FoxFireCZ',
                'FoxFireCZ',
                'FoxFire_AD',
                '@FoxFireCZ'
            )
        ];
    }
}

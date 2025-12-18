<?php

namespace App\Repository;

use App\Model\FeaturesStatusEnum;
use App\Model\WebsiteFeatures;

class WebsiteFeaturesRepository
{
    public function findAll(): array
    {
        return [
            new WebsiteFeatures(
                1,
                'Task list',
                'List where you can add and delete tasks.',
                FeaturesStatusEnum::NEW_FEATURE
            ),
            new WebsiteFeatures(
                2,
                'Text minigame',
                'A small text minigame with level, hp, damage and a few locations with some monters',
                FeaturesStatusEnum::NEW_FEATURE
            ),
            new WebsiteFeatures(
                3,
                'Personal skills',
                'A page with my own skills about IT',
                FeaturesStatusEnum::NEW_FEATURE
            ),
            new WebsiteFeatures(
                4,
                'User interface',
                'Login, logout and registration for users',
                FeaturesStatusEnum::NEW_FEATURE
            ),
            new WebsiteFeatures(
                5,
                'Better interface visual',
                'Add some colors and design for my website',
                FeaturesStatusEnum::NEW_FEATURE
            ),
            new WebsiteFeatures(
                6,
                'Navigation bar',
                'Add a navigation bar for the website for better access across my website',
                FeaturesStatusEnum::NEW_FEATURE
            ),
            new WebsiteFeatures(
                7,
                'About me',
                'Improve information with links for better access to my social media',
                FeaturesStatusEnum::UPDATE_FEATURE
            ),
            new WebsiteFeatures(
                8,
                'About me',
                'You can find a contact informations on me here and my social medias',
                FeaturesStatusEnum::COMPLETED_FEATURE
            )
        ];
    }
}

<?php

namespace App\Repository;

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
                'New feature'
            ),
            new WebsiteFeatures(
                2,
                'Text minigame',
                'A small text minigame with level, hp, damage and a few locations with some monters',
                'New feature'
            ),
            new WebsiteFeatures(
                3,
                'Personal skills',
                'A page with my own skills about IT',
                'New feature'
            ),
            new WebsiteFeatures(
                4,
                'User interface',
                'Login, logout and registration for users',
                'New feature'
            ),
            new WebsiteFeatures(
                5,
                'Better interface visual',
                'Add some colors and design for my website',
                'New feature'
            ),
            new WebsiteFeatures(
                6,
                'Navigation bar',
                'Add a navigation bar for the website for better access across my website',
                'New feature'
            ),
            new WebsiteFeatures(
                7,
                'About me',
                'Improve information with links for better access to my social media',
                'Update feature'
            )
        ];
    }
}

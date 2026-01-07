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
                'Task list creating.',
                'List where you can add new tasks.',
                FeaturesStatusEnum::UPDATE_FEATURE
            ),
            new WebsiteFeatures(
                2,
                'Basic texting minigame',
                'A small text minigame with level, hp, damage and a few locations with some monters.
                Every location will have a different monsters and after a fight there will be a cooldown for a few minutes.',
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
                'Login, logout and registration for users.',
                FeaturesStatusEnum::NEW_FEATURE
            ),
            new WebsiteFeatures(
                5,
                'Better interface visual',
                'Add some colors and backgrounds design for the website.',
                FeaturesStatusEnum::COMPLETED_FEATURE
            ),
            new WebsiteFeatures(
                6,
                'Navigation bar',
                'Add a navigation bar for the website for better access across my website.',
                FeaturesStatusEnum::COMPLETED_FEATURE
            ),
            new WebsiteFeatures(
                7,
                'About me - add links',
                'Improve information about me with links for better access to my social media.',
                FeaturesStatusEnum::COMPLETED_FEATURE
            ),
            new WebsiteFeatures(
                8,
                'About me',
                'You can find a simple list contact informations about me and my social medias.',
                FeaturesStatusEnum::COMPLETED_FEATURE
            ),
            new WebsiteFeatures(
                9,
                'Task detailed list',
                'Page with chosen task with its description and status.',
                FeaturesStatusEnum::COMPLETED_FEATURE
            ),
            new WebsiteFeatures(
                10,
                'Task list priority',
                'Tasks will have another attribute = priority. There will be five priorities.
                Nice to have (grey), Anytime (white), Soon (Yellow), High priority (Orange), ASAP (Red).',
                FeaturesStatusEnum::UPDATE_FEATURE
            ),
            new WebsiteFeatures(
                11,
                'About me - improve visual design',
                'Add some borders and and text visual improvement. Also improve link so it can be recognized it is link.',
                FeaturesStatusEnum::UPDATE_FEATURE
            ),
        ];
    }

    public function findOne(int $id): ?WebsiteFeatures
    {
        foreach ($this->findAll() as $websiteFeature) {
            if ($websiteFeature->getId() === $id) {
                return $websiteFeature;
            }
        }

        return null;
    }
}

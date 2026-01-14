<?php

namespace App\Repository;

use App\Model\FeaturesStatusEnum;
use App\Model\FeaturePriorityEnum;
use App\Model\WebsiteFeatures;

class WebsiteFeaturesRepository
{
    public function findAll(): array
    {
        return [
            new WebsiteFeatures(
                1,
                'Feature list creating.',
                'List where you can add new features.',
                FeaturesStatusEnum::UPDATE_FEATURE,
                FeaturePriorityEnum::ASAP
            ),
            new WebsiteFeatures(
                2,
                'Basic texting minigame',
                'A small text minigame with level, hp, damage and a few locations with some monters.
                Every location will have a different monsters and after a fight there will be a cooldown for a few minutes.',
                FeaturesStatusEnum::NEW_FEATURE,
                FeaturePriorityEnum::ANYTIME
            ),
            new WebsiteFeatures(
                3,
                'Personal skills',
                'A page with my own skills about IT',
                FeaturesStatusEnum::COMPLETED_FEATURE,
                FeaturePriorityEnum::SOON
            ),
            new WebsiteFeatures(
                4,
                'User interface',
                'Login, logout and registration for users.',
                FeaturesStatusEnum::NEW_FEATURE,
                FeaturePriorityEnum::HIGH_PRIORITY
            ),
            new WebsiteFeatures(
                5,
                'Better interface visual',
                'Add some colors and backgrounds design for the website.',
                FeaturesStatusEnum::COMPLETED_FEATURE,
                FeaturePriorityEnum::HIGH_PRIORITY
            ),
            new WebsiteFeatures(
                6,
                'Navigation bar',
                'Add a navigation bar for the website for better access across my website.',
                FeaturesStatusEnum::COMPLETED_FEATURE,
                FeaturePriorityEnum::ASAP
            ),
            new WebsiteFeatures(
                7,
                'About me - add links',
                'Improve information about me with links for better access to my social media.',
                FeaturesStatusEnum::COMPLETED_FEATURE,
                FeaturePriorityEnum::ANYTIME
            ),
            new WebsiteFeatures(
                8,
                'About me',
                'You can find a simple list contact informations about me and my social medias.',
                FeaturesStatusEnum::COMPLETED_FEATURE,
                FeaturePriorityEnum::SOON
            ),
            new WebsiteFeatures(
                9,
                'Feature detailed list',
                'Page with chosen feature with its description and status.',
                FeaturesStatusEnum::COMPLETED_FEATURE,
                FeaturePriorityEnum::HIGH_PRIORITY
            ),
            new WebsiteFeatures(
                10,
                'Features list priority',
                'Features will have another attribute = priority. There will be five priorities.
                Nice to have (grey), Anytime (white), Soon (Yellow), High priority (Orange), ASAP (Red).',
                FeaturesStatusEnum::COMPLETED_FEATURE,
                FeaturePriorityEnum::ANYTIME
            ),
            new WebsiteFeatures(
                11,
                'About me - improve visual design',
                'Add some borders and and text visual improvement. Also improve link so it can be recognized it is link.',
                FeaturesStatusEnum::COMPLETED_FEATURE,
                FeaturePriorityEnum::NICE_TO_HAVE
            ),
            new WebsiteFeatures(
                12,
                'Features - add deadlines',
                'With this update I can add deadlines so I can check how long much time I have until I will have to finish your feature.',
                FeaturesStatusEnum::UPDATE_FEATURE,
                FeaturePriorityEnum::ANYTIME
            ),
            new WebsiteFeatures(
                13,
                'Improve website with database',
                'Add database to store important data and improve possibilities I can do on my website.',
                FeaturesStatusEnum::NEW_FEATURE,
                FeaturePriorityEnum::ASAP
            ),
            new WebsiteFeatures(
                14,
                'Add cook recipes',
                'There will be a page with my recipes that I tried. For now only names of recipes.',
                FeaturesStatusEnum::COMPLETED_FEATURE,
                FeaturePriorityEnum::ANYTIME
            ),
            new WebsiteFeatures(
                15,
                'Better navigation menu',
                'Add some border and light effects for navigation menu.',
                FeaturesStatusEnum::UPDATE_FEATURE,
                FeaturePriorityEnum::SOON
            ),
            new WebsiteFeatures(
                16,
                'Rename tasks on Web Futures',
                'Rename tasks on Web Futures. It is because in the future I want to create tasks for daily rutine.',
                FeaturesStatusEnum::COMPLETED_FEATURE,
                FeaturePriorityEnum::HIGH_PRIORITY
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

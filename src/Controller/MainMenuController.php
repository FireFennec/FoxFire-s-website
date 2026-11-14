<?php

declare(strict_types=1);

namespace App\Controller;

use App\Model\FeaturesStatusEnum;
use App\Repository\WebsiteFeatureRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Handles the homepage and displays an overview of website features.
 */
class MainMenuController extends AbstractController
{
    /**
     * Displays website features grouped by status and ordered by priority.
     */
    #[Route('/', name: 'app_mainMenu', methods: ['GET'])]
    public function mainMenu(
        WebsiteFeatureRepository $websiteFeaturesRepository
    ): Response {
        return $this->render('main/mainMenu.html.twig', [
            'newFeatures' => $websiteFeaturesRepository
                ->findByStatusOrderedByPriority(
                    FeaturesStatusEnum::NEW_FEATURE
                ),
            'updateFeatures' => $websiteFeaturesRepository
                ->findByStatusOrderedByPriority(
                    FeaturesStatusEnum::UPDATE_FEATURE
                ),
            'completedFeatures' => $websiteFeaturesRepository
                ->findRecentlyCompleted(),
            'completedFeaturesCount' => $websiteFeaturesRepository
                ->count([
                    'status' => FeaturesStatusEnum::COMPLETED_FEATURE,
                ]),
        ]);
    }
}

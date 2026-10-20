<?php

declare(strict_types=1);

namespace App\Controller;

use App\Model\FeaturesStatusEnum;
use App\Repository\WebsiteFeatureRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class MainMenuController extends AbstractController
{
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
                ->findByStatusOrderedByPriority(
                    FeaturesStatusEnum::COMPLETED_FEATURE
                ),
        ]);
    }
}

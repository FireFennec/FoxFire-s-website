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
    // Display the homepage.
    #[Route('/', name: 'app_mainMenu', methods: ['GET'])]
    public function mainMenu(WebsiteFeatureRepository $websiteFeaturesRepository): Response
    {
        // Initialize arrays for features grouped by their status.
        $newFeatures = [];
        $updateFeatures = [];
        $completedFeatures = [];

        // Retrieve all website features ordered by priority.
        $websiteFeatures = $websiteFeaturesRepository->findAll();

        // Sort features into separate arrays based on their status.
        foreach ($websiteFeatures as $websiteFeature) {
            $status = $websiteFeature->getStatus();

            if ($status === FeaturesStatusEnum::NEW_FEATURE) {
                $newFeatures[] = $websiteFeature;
            } elseif ($status === FeaturesStatusEnum::UPDATE_FEATURE) {
                $updateFeatures[] = $websiteFeature;
            } elseif ($status === FeaturesStatusEnum::COMPLETED_FEATURE) {
                $completedFeatures[] = $websiteFeature;
            }
        }

        // Render the homepage and pass the grouped features to Twig.
        return $this->render('main/mainMenu.html.twig', [
            'newFeatures' => $newFeatures,
            'updateFeatures' => $updateFeatures,
            'completedFeatures' => $completedFeatures,
        ]);
    }
}

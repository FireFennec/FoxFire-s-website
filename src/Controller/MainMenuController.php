<?php

namespace App\Controller;

use App\Model\FeaturesStatusEnum;
use App\Repository\WebsiteFeatureRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class MainMenuController extends AbstractController
{
    #[Route('/', name: 'app_mainMenu', methods: ['GET'])]
    public function mainMenu(WebsiteFeatureRepository $websiteFeaturesRepository): Response
    {
        $newFeatures = [];
        $updateFeatures = [];
        $completedFeatures = [];

        $websiteFeatures = $websiteFeaturesRepository->findAll();

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

        return $this->render('main/mainMenu.html.twig', [
            'newFeatures' => $newFeatures,
            'updateFeatures' => $updateFeatures,
            'completedFeatures' => $completedFeatures,
        ]);
    }
}

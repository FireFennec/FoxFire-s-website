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

        foreach ($websiteFeatures as $websiteFeature)
        {
            if ($websiteFeature->getStatus() === FeaturesStatusEnum::NEW_FEATURE){
                $newFeatures[] = $websiteFeature;
            } else if ($websiteFeature->getStatus() === FeaturesStatusEnum::UPDATE_FEATURE){
                $updateFeatures[] = $websiteFeature;
            } else if ($websiteFeature->getStatus() === FeaturesStatusEnum::COMPLETED_FEATURE){
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

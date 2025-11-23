<?php

namespace App\Controller;

use App\Repository\WebsiteFeaturesRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class MainMenuController extends AbstractController
{
    #[Route('/')]
    public function mainMenu(WebsiteFeaturesRepository $websiteFeaturesRepository): Response
    {
        $websiteFeatures = $websiteFeaturesRepository->findAll();

        $newFeatures = [];
        $updateFeatures = [];
        $completedFeatures = [];
        $newFeaturesCount = 0;
        $updateFeaturesCount = 0;
        $completedFeaturesCount = 0;
        foreach ($websiteFeatures as $websiteFeature)
        {
            if ($websiteFeature->getType() === 'New feature'){
                $newFeatures[] = $websiteFeature;
                $newFeaturesCount++;
            } else if ($websiteFeature->getType() === 'Update feature'){
                $updateFeatures[] = $websiteFeature;
                $updateFeaturesCount++;
            } else if ($websiteFeature->getType() === 'Completed feature'){
                $completedFeatures[] = $websiteFeature;
                $completedFeaturesCount++;
            }
        }

        return $this->render('main/mainMenu.html.twig', [
            'newFeatures' => $newFeatures,
            'updateFeatures' => $updateFeatures,
            'completedFeatures' => $completedFeatures,
            'newFeaturesCount' => $newFeaturesCount,
            'updateFeaturesCount' => $updateFeaturesCount,
            'completedFeaturesCount' => $completedFeaturesCount,
        ]);
    }
}

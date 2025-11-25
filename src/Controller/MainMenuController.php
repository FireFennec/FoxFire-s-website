<?php

namespace App\Controller;

use App\Repository\WebsiteFeaturesRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class MainMenuController extends AbstractController
{
    #[Route('/', name: 'app_mainMenu', methods: ['GET'])]
    public function mainMenu(WebsiteFeaturesRepository $websiteFeaturesRepository): Response
    {
        $websiteFeatures = $websiteFeaturesRepository->findAll();

        $newFeatures = [];
        $updateFeatures = [];
        $completedFeatures = [];
        foreach ($websiteFeatures as $websiteFeature)
        {
            if ($websiteFeature->getType() === 'New feature'){
                $newFeatures[] = $websiteFeature;
            } else if ($websiteFeature->getType() === 'Update feature'){
                $updateFeatures[] = $websiteFeature;
            } else if ($websiteFeature->getType() === 'Completed feature'){
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

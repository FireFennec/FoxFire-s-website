<?php

namespace App\Controller;

use App\Model\FeaturesStatusEnum;
use App\Repository\WebsiteFeaturesRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/feature')]
class FeaturesListController extends AbstractController
{
    #[Route('/{id<\d+>}', name: 'app_featureList_featureInfoShow')]
    public function taskInfoShow($id, WebsiteFeaturesRepository $repository): Response
    {
        $feature = $repository->findOne($id);

        if (!$feature) {
            throw $this->createNotFoundException('Feature not found');
        }

        return $this->render('webFeatures/featuresInfoShow.html.twig', [
            'feature' => $feature
        ]);
    }

    #[Route('/list', name: 'app_featureList_featureList')]
    public function taskList(WebsiteFeaturesRepository $repository): Response
    {
        $featureList = $repository->findAll();

        foreach ($featureList as $feature) {
            if ($feature->getStatus() === FeaturesStatusEnum::COMPLETED_FEATURE) {
                $completedFeatures[] = $feature;
            } else {
                $toDoFeatures[] = $feature;
            }
        }

        return $this->render('webFeatures/featuresList.html.twig', [
            'completedFeatures' => $completedFeatures,
            'toDoFeatures' => $toDoFeatures
        ]);
    }
}

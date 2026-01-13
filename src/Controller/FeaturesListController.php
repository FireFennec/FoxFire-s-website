<?php

namespace App\Controller;

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
}

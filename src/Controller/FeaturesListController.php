<?php

namespace App\Controller;

use App\Entity\WebsiteFeature;
use App\Form\Features\CreateFeatureForm;
use App\Model\FeaturesStatusEnum;
use App\Repository\WebsiteFeatureRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/feature')]
class FeaturesListController extends AbstractController
{
    # Displays the feature list split into completed and to-do features.
    #[Route('/list', name: 'app_FeaturesList_featureListMenu', methods: ['GET'])]
    public function featuresListMenu(WebsiteFeatureRepository $repository): Response
    {
        $features = $repository->findAll();

        $completedFeatures = [];
        $toDoFeatures = [];
        foreach ($features as $feature) {
            if ($feature->getStatus() === FeaturesStatusEnum::COMPLETED_FEATURE) {
                $completedFeatures[] = $feature;
            } else {
                $toDoFeatures[] = $feature;
            }
        }

        return $this->render('webFeatures/featuresListMenu.html.twig', [
            'completedFeatures' => $completedFeatures,
            'toDoFeatures' => $toDoFeatures
        ]);
    }

    #[Route('/{id<\d+>}', name: 'app_FeatureList_featureInfoShow')]
    public function featureInfoShow(int $id, WebsiteFeatureRepository $repository): Response
    {
        $feature = $repository->findOne($id);

        if (!$feature) {
            throw $this->createNotFoundException('Feature not found');
        }

        return $this->render('webFeatures/featuresInfoShow.html.twig', [
            'feature' => $feature
        ]);
    }

    #[Route('/createFeature', name: 'app_featureList_createFeature')]
    public function createFeature(Request $request, EntityManagerInterface $entityManager): Response
    {
        $feature = new WebsiteFeature();

        $form = $this->createForm(CreateFeatureForm::class , $feature, [
            'submit_label' => 'Create Feature',
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($feature);
            $entityManager->flush();

            return $this->redirectToRoute('app_FeatureList_featureInfoShow', ['id' => $feature->getId()]);
        }

        return $this->render('webFeatures/createFeature.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/editFeature/{id<\d+>}', name: 'app_featureList_editFeature')]
    public function editFeature(
        $id,
        Request $request,
        EntityManagerInterface $entityManager,
        WebsiteFeatureRepository $repository
    ): Response
    {
        $feature = $repository->findOne($id);

        // Return a 404 response if the feature does not exist.
        if (!$feature) {
            throw $this->createNotFoundException('Feature not found');
        }

        $form = $this->createForm(CreateFeatureForm::class, $feature, [
            'submit_label' => 'Edit Feature',
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_FeatureList_featureInfoShow', ['id' => $feature->getId()]);
        }

        return $this->render('webFeatures/editFeature.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/deleteFeature/{id<\d+>}', name: 'app_featureDelete', methods: ['POST'])]
    public function deleteFeature(
        int $id,
        Request $request,
        WebsiteFeatureRepository $repository,
        EntityManagerInterface $entityManager
    ): Response {
        $feature = $repository->findOne($id);

        if (!$feature) {
            throw $this->createNotFoundException('Feature not found');
        }

        // Verify the CSRF token before deleting the feature.
        if (!$this->isCsrfTokenValid(
            'delete_feature_' . $feature->getId(),
            $request->request->getString('_token')
        )) {
            throw $this->createAccessDeniedException('Invalid CSRF token.');
        }

        // Remove the feature from the database.
        $entityManager->remove($feature);
        $entityManager->flush();

        return $this->redirectToRoute('app_FeaturesList_featureListMenu');
    }

}

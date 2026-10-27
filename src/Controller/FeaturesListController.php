<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\WebsiteFeature;
use App\Form\Features\CreateFeatureForm;
use App\Repository\WebsiteFeatureRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

/**
 * Manages website features, including their listing, creation,
 * editing, viewing, and deletion.
 */
#[Route('/feature')]
class FeaturesListController extends AbstractController
{
    /**
     * Displays website features grouped by completion status.
     */
    #[Route('/list', name: 'app_FeaturesList_featureListMenu', methods: ['GET'])]
    public function featuresListMenu(WebsiteFeatureRepository $repository): Response
    {
        return $this->render('webFeatures/featuresListMenu.html.twig', [
            'completedFeatures' => $repository->findCompleted(),
            'toDoFeatures' => $repository->findToDo(),
        ]);
    }

    /**
     * Displays detailed information about a specific website feature.
     */
    #[Route('/{id<\d+>}', name: 'app_FeatureList_featureInfoShow', methods: ['GET'])]
    public function featureInfoShow(int $id, WebsiteFeatureRepository $repository): Response
    {
        $feature = $repository->findOne($id);

        if (!$feature) {
            throw $this->createNotFoundException('Feature not found');
        }

        return $this->render('webFeatures/featuresInfoShow.html.twig', [
            'feature' => $feature,
        ]);
    }

    /**
     * Displays and processes the form for creating a new website feature.
     */
    #[Route('/createFeature', name: 'app_featureList_createFeature')]
    #[IsGranted('ROLE_ADMIN')]
    public function createFeature(Request $request, EntityManagerInterface $entityManager): Response
    {
        $feature = new WebsiteFeature();

        $form = $this->createForm(CreateFeatureForm::class, $feature, [
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

    /**
     * Displays and processes the form for editing an existing website feature.
     */
    #[Route('/editFeature/{id<\d+>}', name: 'app_featureList_editFeature')]
    #[IsGranted('ROLE_ADMIN')]
    public function editFeature(
        int $id,
        Request $request,
        EntityManagerInterface $entityManager,
        WebsiteFeatureRepository $repository
    ): Response {
        $feature = $repository->findOne($id);

        if (!$feature) {
            throw $this->createNotFoundException('Feature not found');
        }

        $form = $this->createForm(CreateFeatureForm::class, $feature, [
            'submit_label' => 'Edit Feature',
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // The existing entity is already managed by Doctrine.
            $entityManager->flush();

            return $this->redirectToRoute('app_FeatureList_featureInfoShow', ['id' => $feature->getId()]);
        }

        return $this->render('webFeatures/editFeature.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * Deletes an existing website feature after validating the CSRF token.
     */
    #[Route('/deleteFeature/{id<\d+>}', name: 'app_featureDelete', methods: ['POST'])]
    #[IsGranted('ROLE_ADMIN')]
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

        if (!$this->isCsrfTokenValid(
            'delete_feature_' . $feature->getId(),
            $request->request->getString('_token')
        )) {
            throw $this->createAccessDeniedException('Invalid CSRF token.');
        }

        $entityManager->remove($feature);
        $entityManager->flush();

        return $this->redirectToRoute('app_FeaturesList_featureListMenu');
    }
}

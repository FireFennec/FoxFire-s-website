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

#[Route('/feature')]
class FeaturesListController extends AbstractController
{
    // Display the feature list split into completed and to-do features.
    #[Route('/list', name: 'app_FeaturesList_featureListMenu', methods: ['GET'])]
    public function featuresListMenu(WebsiteFeatureRepository $repository): Response
    {
        // Retrieve features grouped by their completion status.
        return $this->render('webFeatures/featuresListMenu.html.twig', [
            'completedFeatures' => $repository->findCompleted(),
            'toDoFeatures' => $repository->findToDo(),
        ]);
    }

    // Display detailed information about a specific feature.
    #[Route('/{id<\d+>}', name: 'app_FeatureList_featureInfoShow')]
    public function featureInfoShow(int $id, WebsiteFeatureRepository $repository): Response
    {
        // Retrieve the feature by its ID.
        $feature = $repository->findOne($id);

        // Return a 404 response if the feature does not exist.
        if (!$feature) {
            throw $this->createNotFoundException('Feature not found');
        }

        // Render the feature detail page.
        return $this->render('webFeatures/featuresInfoShow.html.twig', [
            'feature' => $feature
        ]);
    }

    // Display and process the form for creating a new feature.
    #[Route('/createFeature', name: 'app_featureList_createFeature')]
    public function createFeature(Request $request, EntityManagerInterface $entityManager): Response
    {
        // Create a new feature entity and its form.
        $feature = new WebsiteFeature();

        $form = $this->createForm(CreateFeatureForm::class , $feature, [
            'submit_label' => 'Create Feature',
        ]);

        // Process the incoming request and submitted form data.
        $form->handleRequest($request);

        // Save the new feature if the form was submitted and is valid.
        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($feature);
            $entityManager->flush();

            // Redirect to the newly created feature's detail page.
            return $this->redirectToRoute('app_FeatureList_featureInfoShow', ['id' => $feature->getId()]);
        }

        // Display the form when it has not been submitted or contains errors.
        return $this->render('webFeatures/createFeature.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    // Display and process the form for editing an existing feature.
    #[Route('/editFeature/{id<\d+>}', name: 'app_featureList_editFeature')]
    public function editFeature(
        int $id,
        Request $request,
        EntityManagerInterface $entityManager,
        WebsiteFeatureRepository $repository
    ): Response
    {
        // Retrieve the existing feature by its ID.
        $feature = $repository->findOne($id);

        // Return a 404 response if the feature does not exist.
        if (!$feature) {
            throw $this->createNotFoundException('Feature not found');
        }

        // Create a form populated with the existing feature data.
        $form = $this->createForm(CreateFeatureForm::class, $feature, [
            'submit_label' => 'Edit Feature',
        ]);

        // Process the incoming request and update the feature entity.
        $form->handleRequest($request);

        // Save changes to the existing feature if the form is valid.
        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            // Redirect to the updated feature's detail page.
            return $this->redirectToRoute('app_FeatureList_featureInfoShow', ['id' => $feature->getId()]);
        }

        // Display the edit form when it has not been submitted or contains errors.
        return $this->render('webFeatures/editFeature.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    // Delete an existing feature using a POST request.
    #[Route('/deleteFeature/{id<\d+>}', name: 'app_featureDelete', methods: ['POST'])]
    public function deleteFeature(
        int $id,
        Request $request,
        WebsiteFeatureRepository $repository,
        EntityManagerInterface $entityManager
    ): Response {
        // Retrieve the feature that should be deleted.
        $feature = $repository->findOne($id);

        // Return a 404 response if the feature does not exist.
        if (!$feature) {
            throw $this->createNotFoundException('Feature not found');
        }

        // Verify the CSRF token to prevent unauthorized deletion requests.
        if (!$this->isCsrfTokenValid(
            'delete_feature_' . $feature->getId(),
            $request->request->getString('_token')
        )) {
            throw $this->createAccessDeniedException('Invalid CSRF token.');
        }

        // Remove the feature from the database.
        $entityManager->remove($feature);
        $entityManager->flush();

        // Redirect back to the feature list after deletion.
        return $this->redirectToRoute('app_FeaturesList_featureListMenu');
    }
}

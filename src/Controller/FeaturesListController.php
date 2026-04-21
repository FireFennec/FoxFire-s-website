<?php

namespace App\Controller;

use App\Entity\WebsiteFeature;
use App\Form\Features\CreateFeatureForm;
use App\Model\FeaturesStatusEnum;
use App\Repository\WebsiteFeaturesRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
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

            return $this->redirectToRoute('app_featureList_featureInfoShow', ['id' => $feature->getId()]);
        }

        return $this->render('webFeatures/createFeature.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/editFeature/{id<\d+>}', name: 'app_featureEdit_editFeature')]
    public function editFeature(
        $id,
        Request $request,
        EntityManagerInterface $entityManager,
        WebsiteFeaturesRepository $repository
    ): Response
    {
        $feature = $repository->findOne($id);

        $form = $this->createForm(CreateFeatureForm::class , $feature, [
            'submit_label' => 'Edit Feature',
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($feature);
            $entityManager->flush();

            return $this->redirectToRoute('app_featureList_featureInfoShow', ['id' => $feature->getId()]);
        }

        return $this->render('webFeatures/editFeature.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/deleteFeature/{id<\d+>}', name: 'app_featureDelete')]
    public function deleteFeature($id, WebsiteFeaturesRepository $repository, EntityManagerInterface $entityManager,): Response
    {
        $feature = $repository->findOne($id);
        if (!$feature) {
            throw $this->createNotFoundException('Feature not found');
        } else {
            $entityManager->remove($feature);
            $entityManager->flush();
        }
        return $this->redirectToRoute('app_featureList_featureList');
    }
}

<?php

namespace App\Controller;

use App\Entity\CookingRecipe;
use App\Form\CookingRecipes\CreateRecipeForm;
use App\Repository\CookingRecipesRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/cooking-recipes')]
class CookingRecipeController extends AbstractController
{
    #[Route('/', name: 'app_cooking_recipe', methods: ['GET'])]
    public function listOfRecipes(CookingRecipesRepository $repository): Response
    {
        $recipes = $repository->findAll();

        return $this->render('cookingRecipe/listOfRecipes.html.twig', [
            'recipes' => $recipes
        ]);
    }

    #[Route('createRecipe', name: 'app_cooking_recipe_create')]
    public function createRecipe(Request $request, EntityManagerInterface $entityManager): Response
    {
        $cookingRecipe = new CookingRecipe();

        $form = $this->createForm(CreateRecipeForm::Class, $cookingRecipe, [
            'submit_label' => 'Create Recipe',
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($cookingRecipe);
            $entityManager->flush();

            return $this->redirectToRoute('app_cooking_recipe');
        }

        return $this->render('cookingRecipe/createRecipe.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/editCookingRecipe/{id<\d+>}', name: 'app_cookingRecipe_editCookingRecipe', methods: ['GET'])]
    public function editCookingRecipe(
        $id,
        Request $request,
        EntityManagerInterface $entityManager,
        CookingRecipesRepository $repository,
    ): Response
    {
        $cookingRecipe = $repository->findOne($id);

        $form = $this->createForm(CreateRecipeForm::Class, $cookingRecipe, [
            'submit_label' => 'Edit Recipe',
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($cookingRecipe);
            $entityManager->flush();

            return $this->redirectToRoute('app_cooking_recipe_showRecipeInfo', ['id' => $cookingRecipe->getId()]);
        }

        return $this->render('cookingRecipe/editCookingRecipe.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id<\d+>}', name: 'app_cooking_recipe_showRecipeInfo')]
    public function showRecipeInfo($id, CookingRecipesRepository $repository): Response
    {
        $cookingRecipe = $repository->findOne($id);

        if (!$cookingRecipe) {
            throw $this->createNotFoundException('Cooking recipe not found');
        }

        return $this->render('cookingRecipe/showRecipeInfo.html.twig', [
            'cookingRecipe' => $cookingRecipe
        ]);
    }
}

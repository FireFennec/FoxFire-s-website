<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\CookingRecipe;
use App\Form\CookingRecipes\CookingRecipeType;
use App\Repository\CookingRecipeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Pagerfanta\Doctrine\ORM\QueryAdapter;
use Pagerfanta\Pagerfanta;

#[Route('/cooking-recipes')]
class CookingRecipeController extends AbstractController
{
    // Display all cooking recipes.
    #[Route('/', name: 'app_cooking_recipe', methods: ['GET'])]
    public function listOfRecipes(
        Request $request,
        CookingRecipeRepository $repository
    ): Response {
        $queryBuilder = $repository->createOrderedQueryBuilder();

        $pager = new Pagerfanta(new QueryAdapter($queryBuilder));
        $pager->setMaxPerPage(10);

        // Normalize invalid page numbers to the first page.
        $page = $request->query->getInt('page', 1);
        $page = max(1, $page);

        // Prevent requesting a page beyond the available results.
        $page = min($page, max(1, $pager->getNbPages()));

        $pager->setCurrentPage($page);

        return $this->render('cookingRecipe/listOfRecipes.html.twig', [
            'recipes' => $pager,
        ]);
    }

    // Display and process the recipe creation form.
    #[Route('/create', name: 'app_cooking_recipe_create', methods: ['GET', 'POST'])]
    public function createRecipe(
        Request $request,
        EntityManagerInterface $entityManager
    ): Response {
        $cookingRecipe = new CookingRecipe();

        $form = $this->createForm(CookingRecipeType::class, $cookingRecipe, [
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

    // Display and process the recipe editing form.
    #[Route('/{id<\d+>}/edit', name: 'app_cookingRecipe_editCookingRecipe', methods: ['GET', 'POST'])]
    public function editCookingRecipe(
        int $id,
        Request $request,
        EntityManagerInterface $entityManager,
        CookingRecipeRepository $repository
    ): Response {
        $cookingRecipe = $repository->find($id);

        if ($cookingRecipe === null) {
            throw $this->createNotFoundException('Cooking recipe not found.');
        }

        $form = $this->createForm(CookingRecipeType::class, $cookingRecipe, [
            'submit_label' => 'Save Changes',
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Doctrine already manages the existing entity.
            $entityManager->flush();

            return $this->redirectToRoute(
                'app_cooking_recipe_showRecipeInfo',
                ['id' => $cookingRecipe->getId()]
            );
        }

        return $this->render('cookingRecipe/editCookingRecipe.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    // Display the details of a single cooking recipe.
    #[Route('/{id<\d+>}', name: 'app_cooking_recipe_showRecipeInfo', methods: ['GET'])]
    public function showRecipeInfo(
        int $id,
        CookingRecipeRepository $repository
    ): Response {
        $cookingRecipe = $repository->find($id);

        if ($cookingRecipe === null) {
            throw $this->createNotFoundException('Cooking recipe not found.');
        }

        return $this->render('cookingRecipe/showRecipeInfo.html.twig', [
            'cookingRecipe' => $cookingRecipe,
        ]);
    }
}

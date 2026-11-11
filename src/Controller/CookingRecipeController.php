<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\CookingRecipe;
use App\Form\CookingRecipes\CookingRecipeType;
use App\Repository\CookingRecipeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Pagerfanta\Doctrine\ORM\QueryAdapter;
use Pagerfanta\Pagerfanta;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

/**
 * Handles the listing, viewing, creation, and editing of cooking recipes.
 */
#[Route('/cooking-recipes')]
class CookingRecipeController extends AbstractController
{
    /**
     * Displays a paginated list of cooking recipes.
     */
    #[Route('/', name: 'app_cooking_recipe', methods: ['GET'])]
    public function listOfRecipes(
        Request $request,
        CookingRecipeRepository $repository
    ): Response {
        $queryBuilder = $repository->createOrderedQueryBuilder();

        $pager = new Pagerfanta(new QueryAdapter($queryBuilder));
        $pager->setMaxPerPage(10);

        // Keep the requested page within the available range.
        $page = $request->query->getInt('page', 1);
        $page = max(1, $page);
        $page = min($page, max(1, $pager->getNbPages()));

        $pager->setCurrentPage($page);

        return $this->render('cookingRecipe/listOfRecipes.html.twig', [
            'recipes' => $pager,
        ]);
    }

    /**
     * Displays and processes the form for creating a new cooking recipe.
     */
    #[Route('/create', name: 'app_cooking_recipe_create', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_COOKING_ADMIN')]
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

    /**
     * Displays and processes the form for editing an existing cooking recipe.
     */
    #[Route('/{id<\d+>}/edit', name: 'app_cookingRecipe_editCookingRecipe', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_COOKING_ADMIN')]
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
            // The existing entity is already managed by Doctrine.
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

    /**
     * Displays the details of a cooking recipe.
     */
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

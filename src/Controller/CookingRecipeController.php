<?php

namespace App\Controller;

use App\Repository\CookingRecipesRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
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
}

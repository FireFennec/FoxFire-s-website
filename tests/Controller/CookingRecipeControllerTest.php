<?php

declare(strict_types=1);

namespace App\Tests\Controller;

use App\Entity\CookingRecipe;
use App\Repository\CookingRecipeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class CookingRecipeControllerTest extends WebTestCase
{
    public function testRecipeListLoadsSuccessfully(): void
    {
        $client = static::createClient();

        $client->request('GET', '/cooking-recipes/');

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('h1', 'Cooking recipes');
    }

    public function testRecipeListDisplaysSavedRecipe(): void
    {
        $client = static::createClient();

        $recipe = $this->createRecipe();

        $crawler = $client->request('GET', '/cooking-recipes/');

        self::assertResponseIsSuccessful();

        $links = $crawler->filter(
            'a.recipe-name[href="/cooking-recipes/' . $recipe->getId() . '"]'
        );

        self::assertCount(1, $links);
        self::assertSame('Test Mac and Cheese', trim($links->text()));
    }

    public function testRecipeDetailDisplaysCorrectInformation(): void
    {
        $client = static::createClient();

        $recipe = $this->createRecipe();

        $crawler = $client->request(
            'GET',
            '/cooking-recipes/' . $recipe->getId()
        );

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('h1', 'Test Mac and Cheese');

        $ingredients = $crawler
            ->filter('.recipe-ingredients li')
            ->each(static fn ($node): string => trim($node->text()));

        self::assertSame(
            ['Macaroni', 'Cheddar', 'Milk'],
            $ingredients
        );

        self::assertSame(
            'Cook the pasta and prepare the cheese sauce.',
            trim($crawler->filter('.recipe-process')->text())
        );
    }

    public function testMissingRecipeReturns404(): void
    {
        $client = static::createClient();

        $client->request('GET', '/cooking-recipes/999999999');

        self::assertResponseStatusCodeSame(404);
    }

    public function testMissingRecipeEditReturns404(): void
    {
        $client = static::createClient();

        $client->request('GET', '/cooking-recipes/999999999/edit');

        self::assertResponseStatusCodeSame(404);
    }

    public function testCreateRecipeFormLoadsSuccessfully(): void
    {
        $client = static::createClient();

        $crawler = $client->request('GET', '/cooking-recipes/create');

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('h1', 'Create Recipe');

        self::assertCount(
            1,
            $crawler->filter('input[name="cooking_recipe[recipeName]"]')
        );

        self::assertCount(
            1,
            $crawler->filter('textarea[name="cooking_recipe[cookingProcess]"]')
        );

        // Verify that CSRF protection is rendered.
        self::assertCount(
            1,
            $crawler->filter('input[name="cooking_recipe[_token]"]')
        );
    }

    public function testValidRecipeCanBeCreated(): void
    {
        $client = static::createClient();

        $crawler = $client->request('GET', '/cooking-recipes/create');

        $token = $crawler
            ->filter('input[name="cooking_recipe[_token]"]')
            ->attr('value');

        $client->request('POST', '/cooking-recipes/create', [
            'cooking_recipe' => [
                'recipeName' => 'New Test Recipe',
                'ingredients' => [
                    'Flour',
                    'Salt, to taste',
                ],
                'cookingProcess' => 'Mix everything together.',
                '_token' => $token,
            ],
        ]);

        self::assertResponseRedirects('/cooking-recipes/');

        $repository = static::getContainer()->get(
            CookingRecipeRepository::class
        );

        $recipe = $repository->findOneBy([
            'recipeName' => 'New Test Recipe',
        ]);

        self::assertNotNull($recipe);
        self::assertSame(
            ['Flour', 'Salt, to taste'],
            $recipe->getIngredients()
        );
        self::assertSame(
            'Mix everything together.',
            $recipe->getCookingProcess()
        );
    }

    public function testInvalidRecipeIsNotCreated(): void
    {
        $client = static::createClient();

        $crawler = $client->request('GET', '/cooking-recipes/create');

        $token = $crawler
            ->filter('input[name="cooking_recipe[_token]"]')
            ->attr('value');

        $client->request('POST', '/cooking-recipes/create', [
            'cooking_recipe' => [
                'recipeName' => '',
                'ingredients' => ['Flour'],
                'cookingProcess' => 'Mix everything.',
                '_token' => $token,
            ],
        ]);

        // Invalid form should be rendered again, not redirected.
        self::assertResponseIsSuccessful();

        $repository = static::getContainer()->get(
            CookingRecipeRepository::class
        );

        self::assertNull(
            $repository->findOneBy([
                'recipeName' => '',
            ])
        );

        self::assertSelectorTextContains(
            'body',
            'Recipe name cannot be empty.'
        );
    }

    public function testExistingRecipeCanBeEdited(): void
    {
        $client = static::createClient();

        $recipe = $this->createRecipe();
        $id = $recipe->getId();

        $crawler = $client->request(
            'GET',
            '/cooking-recipes/' . $id . '/edit'
        );

        self::assertResponseIsSuccessful();

        $token = $crawler
            ->filter('input[name="cooking_recipe[_token]"]')
            ->attr('value');

        $client->request(
            'POST',
            '/cooking-recipes/' . $id . '/edit',
            [
                'cooking_recipe' => [
                    'recipeName' => 'Updated Recipe',
                    'ingredients' => [
                        'Eggs',
                        'Butter',
                    ],
                    'cookingProcess' => 'Cook everything in a pan.',
                    '_token' => $token,
                ],
            ]
        );

        self::assertResponseRedirects('/cooking-recipes/' . $id);

        $entityManager = static::getContainer()->get(
            EntityManagerInterface::class
        );

        // Reload the entity to verify persisted database values.
        $entityManager->clear();

        $repository = static::getContainer()->get(
            CookingRecipeRepository::class
        );

        $updatedRecipe = $repository->find($id);

        self::assertNotNull($updatedRecipe);
        self::assertSame('Updated Recipe', $updatedRecipe->getRecipeName());
        self::assertSame(
            ['Eggs', 'Butter'],
            $updatedRecipe->getIngredients()
        );
        self::assertSame(
            'Cook everything in a pan.',
            $updatedRecipe->getCookingProcess()
        );
    }

    public function testPostRequestToRecipeDetailIsRejected(): void
    {
        $client = static::createClient();

        $recipe = $this->createRecipe();

        $client->request(
            'POST',
            '/cooking-recipes/' . $recipe->getId()
        );

        self::assertResponseStatusCodeSame(405);
    }

    public function testInvalidEditDoesNotChangeStoredRecipe(): void
    {
        $client = static::createClient();
        $recipe = $this->createRecipe();
        $id = $recipe->getId();

        $crawler = $client->request('GET', '/cooking-recipes/' . $id . '/edit');

        $token = $crawler
            ->filter('input[name="cooking_recipe[_token]"]')
            ->attr('value');

        $client->request('POST', '/cooking-recipes/' . $id . '/edit', [
            'cooking_recipe' => [
                'recipeName' => '   ',
                'ingredients' => ['Changed ingredient'],
                'cookingProcess' => 'Changed instructions.',
                '_token' => $token,
            ],
        ]);

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('body', 'Recipe name cannot be empty.');

        $entityManager = static::getContainer()->get(EntityManagerInterface::class);
        $entityManager->clear();

        $storedRecipe = $entityManager->find(CookingRecipe::class, $id);

        self::assertNotNull($storedRecipe);
        self::assertSame('Test Mac and Cheese', $storedRecipe->getRecipeName());
        self::assertSame(
            ['Macaroni', 'Cheddar', 'Milk'],
            $storedRecipe->getIngredients()
        );
        self::assertSame(
            'Cook the pasta and prepare the cheese sauce.',
            $storedRecipe->getCookingProcess()
        );
    }

    public function testInvalidCsrfTokenPreventsRecipeCreation(): void
    {
        $client = static::createClient();

        // Initialize the form and its CSRF protection.
        $client->request('GET', '/cooking-recipes/create');

        $client->request('POST', '/cooking-recipes/create', [
            'cooking_recipe' => [
                'recipeName' => 'Invalid CSRF Recipe',
                'ingredients' => ['Flour'],
                'cookingProcess' => 'Mix everything.',
                '_token' => 'invalid-token',
            ],
        ]);

        self::assertResponseIsSuccessful();

        $repository = static::getContainer()->get(CookingRecipeRepository::class);

        self::assertNull($repository->findOneBy([
            'recipeName' => 'Invalid CSRF Recipe',
        ]));
    }

    public function testInvalidCsrfTokenPreventsRecipeEdit(): void
    {
        $client = static::createClient();
        $recipe = $this->createRecipe();
        $id = $recipe->getId();

        $client->request('GET', '/cooking-recipes/' . $id . '/edit');

        $client->request('POST', '/cooking-recipes/' . $id . '/edit', [
            'cooking_recipe' => [
                'recipeName' => 'Unauthorized Change',
                'ingredients' => ['Changed ingredient'],
                'cookingProcess' => 'Changed instructions.',
                '_token' => 'invalid-token',
            ],
        ]);

        self::assertResponseIsSuccessful();

        $entityManager = static::getContainer()->get(EntityManagerInterface::class);
        $entityManager->clear();

        $storedRecipe = $entityManager->find(CookingRecipe::class, $id);

        self::assertNotNull($storedRecipe);
        self::assertSame('Test Mac and Cheese', $storedRecipe->getRecipeName());
        self::assertSame(
            ['Macaroni', 'Cheddar', 'Milk'],
            $storedRecipe->getIngredients()
        );
    }

    public function testRecipeWithoutIngredientsIsNotCreated(): void
    {
        $client = static::createClient();

        $crawler = $client->request('GET', '/cooking-recipes/create');

        $token = $crawler
            ->filter('input[name="cooking_recipe[_token]"]')
            ->attr('value');

        $client->request('POST', '/cooking-recipes/create', [
            'cooking_recipe' => [
                'recipeName' => 'Recipe Without Ingredients',
                'ingredients' => [],
                'cookingProcess' => 'Cook everything.',
                '_token' => $token,
            ],
        ]);

        self::assertResponseIsSuccessful();

        // Display validation errors for debugging.
        $crawler = $client->getCrawler();

        dump($crawler->filter('.form-error-message')->each(
            static fn ($node): string => trim($node->text())
        ));

        $repository = static::getContainer()->get(
            CookingRecipeRepository::class
        );

        // Verify that the invalid recipe was not saved.
        self::assertNull($repository->findOneBy([
            'recipeName' => 'Recipe Without Ingredients',
        ]));
    }

    public function testWhitespaceOnlyIngredientIsNotAccepted(): void
    {
        $client = static::createClient();

        $crawler = $client->request('GET', '/cooking-recipes/create');

        $token = $crawler
            ->filter('input[name="cooking_recipe[_token]"]')
            ->attr('value');

        $client->request('POST', '/cooking-recipes/create', [
            'cooking_recipe' => [
                'recipeName' => 'Recipe With Blank Ingredient',
                'ingredients' => ['   '],
                'cookingProcess' => 'Cook everything.',
                '_token' => $token,
            ],
        ]);

        self::assertResponseIsSuccessful();

        $repository = static::getContainer()->get(CookingRecipeRepository::class);

        self::assertNull($repository->findOneBy([
            'recipeName' => 'Recipe With Blank Ingredient',
        ]));
    }

    public function testRecipeNameExceedingLimitIsNotAccepted(): void
    {
        $client = static::createClient();

        $crawler = $client->request('GET', '/cooking-recipes/create');

        $token = $crawler
            ->filter('input[name="cooking_recipe[_token]"]')
            ->attr('value');

        $client->request('POST', '/cooking-recipes/create', [
            'cooking_recipe' => [
                'recipeName' => str_repeat('A', 256),
                'ingredients' => ['Flour'],
                'cookingProcess' => 'Mix everything.',
                '_token' => $token,
            ],
        ]);

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains(
            'body',
            'Recipe name cannot exceed 255 characters.'
        );
    }

    public function testPutRequestToCreateRecipeIsRejected(): void
    {
        $client = static::createClient();

        $client->request('PUT', '/cooking-recipes/create');

        self::assertResponseStatusCodeSame(405);
    }

    private function createRecipe(): CookingRecipe
    {
        $entityManager = static::getContainer()->get(
            EntityManagerInterface::class
        );

        $recipe = new CookingRecipe();
        $recipe->setRecipeName('Test Mac and Cheese');
        $recipe->setIngredients([
            'Macaroni',
            'Cheddar',
            'Milk',
        ]);
        $recipe->setCookingProcess(
            'Cook the pasta and prepare the cheese sauce.'
        );

        $entityManager->persist($recipe);
        $entityManager->flush();

        return $recipe;
    }
}

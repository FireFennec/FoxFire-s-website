<?php

declare(strict_types=1);

namespace App\Tests\Entity;

use App\Entity\CookingRecipe;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class CookingRecipeValidationTest extends KernelTestCase
{
    private ValidatorInterface $validator;

    protected function setUp(): void
    {
        parent::setUp();

        self::bootKernel();

        $this->validator = static::getContainer()->get(
            ValidatorInterface::class
        );
    }

    public function testValidRecipePassesValidation(): void
    {
        $recipe = $this->createValidRecipe();

        $violations = $this->validator->validate($recipe);

        self::assertCount(0, $violations, (string) $violations);
    }

    public function testEmptyRecipeNameIsRejected(): void
    {
        $recipe = $this->createValidRecipe();
        $recipe->setRecipeName('');

        $this->assertInvalidField($recipe, 'recipeName');
    }

    public function testWhitespaceOnlyRecipeNameIsRejected(): void
    {
        $recipe = $this->createValidRecipe();
        $recipe->setRecipeName('   ');

        $this->assertInvalidField($recipe, 'recipeName');
    }

    public function testRecipeNameExceeding255CharactersIsRejected(): void
    {
        $recipe = $this->createValidRecipe();
        $recipe->setRecipeName(str_repeat('A', 256));

        $this->assertInvalidField($recipe, 'recipeName');
    }

    public function testRecipeNameWith255CharactersIsAccepted(): void
    {
        $recipe = $this->createValidRecipe();
        $recipe->setRecipeName(str_repeat('A', 255));

        $this->assertValidRecipe($recipe);
    }

    public function testRecipeWithoutIngredientsIsRejected(): void
    {
        $recipe = $this->createValidRecipe();
        $recipe->setIngredients([]);

        $this->assertInvalidField($recipe, 'ingredients');
    }

    public function testEmptyIngredientIsRejected(): void
    {
        $recipe = $this->createValidRecipe();
        $recipe->setIngredients(['Milk', '']);

        $this->assertInvalidField($recipe, 'ingredients[1]');
    }

    public function testWhitespaceOnlyIngredientIsRejected(): void
    {
        $recipe = $this->createValidRecipe();
        $recipe->setIngredients(['   ']);

        $this->assertInvalidField($recipe, 'ingredients[0]');
    }

    public function testIngredientExceeding255CharactersIsRejected(): void
    {
        $recipe = $this->createValidRecipe();
        $recipe->setIngredients([str_repeat('A', 256)]);

        $this->assertInvalidField($recipe, 'ingredients[0]');
    }

    public function testIngredientWithCommaIsAccepted(): void
    {
        $recipe = $this->createValidRecipe();
        $recipe->setIngredients(['Salt, to taste']);

        $this->assertValidRecipe($recipe);
    }

    public function testEmptyCookingProcessIsRejected(): void
    {
        $recipe = $this->createValidRecipe();
        $recipe->setCookingProcess('');

        $this->assertInvalidField($recipe, 'cookingProcess');
    }

    public function testWhitespaceOnlyCookingProcessIsRejected(): void
    {
        $recipe = $this->createValidRecipe();
        $recipe->setCookingProcess('   ');

        $this->assertInvalidField($recipe, 'cookingProcess');
    }

    public function testCookingProcessExceeding10000CharactersIsRejected(): void
    {
        $recipe = $this->createValidRecipe();
        $recipe->setCookingProcess(str_repeat('A', 10001));

        $this->assertInvalidField($recipe, 'cookingProcess');
    }

    public function testCookingProcessWith10000CharactersIsAccepted(): void
    {
        $recipe = $this->createValidRecipe();
        $recipe->setCookingProcess(str_repeat('A', 10000));

        $this->assertValidRecipe($recipe);
    }

    private function createValidRecipe(): CookingRecipe
    {
        return (new CookingRecipe())
            ->setRecipeName('Mac and Cheese')
            ->setIngredients([
                'Macaroni',
                'Cheddar',
                'Milk',
            ])
            ->setCookingProcess('Cook pasta and mix with cheese sauce.');
    }

    private function assertInvalidField(
        CookingRecipe $recipe,
        string $field
    ): void {
        $violations = $this->validator->validate($recipe);

        $invalidFields = [];

        foreach ($violations as $violation) {
            $invalidFields[] = $violation->getPropertyPath();
        }

        self::assertContains(
            $field,
            $invalidFields,
            sprintf('Expected validation error for "%s".', $field)
        );
    }

    private function assertValidRecipe(CookingRecipe $recipe): void
    {
        $violations = $this->validator->validate($recipe);

        self::assertCount(0, $violations, (string) $violations);
    }
}

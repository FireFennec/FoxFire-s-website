<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\CookingRecipeRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Represents a cooking recipe with ingredients and preparation instructions.
 */
#[ORM\Entity(repositoryClass: CookingRecipeRepository::class)]
class CookingRecipe
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(
        message: 'Recipe name cannot be empty.',
        normalizer: 'trim'
    )]
    #[Assert\Length(
        max: 255,
        maxMessage: 'Recipe name cannot exceed {{ limit }} characters.'
    )]
    private ?string $recipeName = null;

    /**
     * @var list<string>
     */
    #[ORM\Column(type: Types::JSON)]
    #[Assert\Count(
        min: 1,
        minMessage: 'Add at least one ingredient.'
    )]
    #[Assert\All([
        new Assert\NotBlank(
            message: 'Ingredient cannot be empty.',
            normalizer: 'trim'
        ),
        new Assert\Length(
            max: 255,
            maxMessage: 'Ingredient cannot exceed {{ limit }} characters.'
        ),
    ])]
    private array $ingredients = [];

    #[ORM\Column(type: Types::TEXT)]
    #[Assert\NotBlank(
        message: 'Cooking process cannot be empty.',
        normalizer: 'trim'
    )]
    #[Assert\Length(
        max: 10000,
        maxMessage: 'Cooking process cannot exceed {{ limit }} characters.'
    )]
    private ?string $cookingProcess = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getRecipeName(): ?string
    {
        return $this->recipeName;
    }

    public function setRecipeName(?string $recipeName): static
    {
        $this->recipeName = $recipeName === null
            ? null
            : trim($recipeName);

        return $this;
    }

    /**
     * @return list<string>
     */
    public function getIngredients(): array
    {
        return $this->ingredients;
    }

    /**
     * @param list<string|null> $ingredients
     */
    public function setIngredients(array $ingredients): static
    {
        $this->ingredients = array_map(
            static fn (?string $ingredient): string => trim($ingredient ?? ''),
            $ingredients
        );

        return $this;
    }

    public function getCookingProcess(): ?string
    {
        return $this->cookingProcess;
    }

    public function setCookingProcess(?string $cookingProcess): static
    {
        $this->cookingProcess = $cookingProcess === null
            ? null
            : trim($cookingProcess);

        return $this;
    }
}

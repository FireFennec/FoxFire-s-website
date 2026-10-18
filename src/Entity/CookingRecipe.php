<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\CookingRecipeRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: CookingRecipeRepository::class)]
class CookingRecipe
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    // Recipe name must not be empty.
    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: 'Recipe name cannot be empty.')]
    #[Assert\Length(
        max: 255,
        maxMessage: 'Recipe name cannot exceed {{ limit }} characters.'
    )]
    private ?string $recipeName = null;

    // A recipe must contain at least one valid ingredient.
    #[ORM\Column(type: Types::JSON)]
    #[Assert\Count(
        min: 1,
        minMessage: 'Add at least one ingredient.'
    )]
    #[Assert\All([
        new Assert\NotBlank(
            message: 'Ingredient cannot be empty.'
        ),
        new Assert\Length(
            max: 255,
            maxMessage: 'Ingredient cannot exceed {{ limit }} characters.'
        ),
    ])]
    private array $ingredience = [];

    // Cooking instructions must not be empty.
    #[ORM\Column(type: Types::TEXT)]
    #[Assert\NotBlank(message: 'Cooking process cannot be empty.')]
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

    public function setRecipeName(string $recipeName): static
    {
        $this->recipeName = $recipeName;

        return $this;
    }

    public function getIngredience(): array
    {
        return $this->ingredience;
    }

    public function setIngredience(array $ingredience): static
    {
        $this->ingredience = $ingredience;

        return $this;
    }

    public function getCookingProcess(): ?string
    {
        return $this->cookingProcess;
    }

    public function setCookingProcess(string $cookingProcess): static
    {
        $this->cookingProcess = $cookingProcess;

        return $this;
    }
}

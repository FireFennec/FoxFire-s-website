<?php

namespace App\Entity;

use App\Repository\CookingRecipeRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CookingRecipeRepository::class)]
class CookingRecipe
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $recipeName = null;

    #[ORM\Column(type: Types::SIMPLE_ARRAY)]
    private array $ingredience = [];

    #[ORM\Column(type: Types::TEXT)]
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

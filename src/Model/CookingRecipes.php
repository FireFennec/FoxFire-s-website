<?php

namespace App\Model;

class CookingRecipes
{
    public function __construct(
        private int $id,
        private string $nameOfRecipe,
    ){
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $id): void
    {
        $this->id = $id;
    }

    public function getNameOfRecipe(): string
    {
        return $this->nameOfRecipe;
    }

    public function setNameOfRecipe(string $nameOfRecipe): void
    {
        $this->nameOfRecipe = $nameOfRecipe;
    }
}

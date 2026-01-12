<?php

namespace App\Repository;

use App\Model\CookingRecipes;

class CookingRecipesRepository
{
    public function findAll(): array
    {
        return [
            new CookingRecipes(
                0,
                'Salmon with salad'
            ),
            new CookingRecipes(
                1,
                'Fettuccine alfredo'
            ),
            new CookingRecipes(
                2,
                'Vegetable salad with tartare sauce garlic'
            ),

        ];
    }
}

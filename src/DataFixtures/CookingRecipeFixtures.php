<?php

namespace App\DataFixtures;

use App\Entity\CookingRecipe;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class CookingRecipeFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $cookingRecipe1 = new CookingRecipe();
        $cookingRecipe1->setRecipeName('Salmon with salad');
        $cookingRecipe1->setCookingProcess('Later');
        $cookingRecipe1->setIngredience([
            'Later',
        ]);

        $cookingRecipe2 = new CookingRecipe();
        $cookingRecipe2->setRecipeName('Fettuccine alfredo');
        $cookingRecipe2->setCookingProcess('Later');
        $cookingRecipe2->setIngredience([
            'Later',
        ]);

        $cookingRecipe3 = new CookingRecipe();
        $cookingRecipe3->setRecipeName('Vegetable salad with tartar sauce garlic');
        $cookingRecipe3->setCookingProcess('Later');
        $cookingRecipe3->setIngredience([
            'Later',
        ]);

        $cookingRecipe4 = new CookingRecipe();
        $cookingRecipe4->setRecipeName('Qurrito');
        $cookingRecipe4->setCookingProcess('Later');
        $cookingRecipe4->setIngredience([
            'Later',
        ]);

        $manager->persist($cookingRecipe1);
        $manager->persist($cookingRecipe2);
        $manager->persist($cookingRecipe3);
        $manager->persist($cookingRecipe4);

        $manager->flush();
    }
}

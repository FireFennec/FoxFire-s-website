<?php

namespace App\Controller;

use Symfony\Component\Routing\Attribute\Route;

class MainMenuController
{
    #[Route('/')]
    public function mainMenu(): array
    {
        return [];
    }
}

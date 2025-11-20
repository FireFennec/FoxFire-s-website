<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class MainMenuController extends AbstractController
{
    #[Route('/')]
    public function mainMenu(): Response
    {
        return $this->render('main/mainMenu.html.twig', []);
    }
}

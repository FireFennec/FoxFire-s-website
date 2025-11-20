<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ContactController extends AbstractController
{
    #[Route('aboutMe')]
    public function aboutMe(): Response
    {
        return $this->render('contact/aboutMe.html.twig');
    }
}

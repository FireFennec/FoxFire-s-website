<?php

namespace App\Controller;

use App\Repository\ContactInformationsRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ContactController extends AbstractController
{
    #[Route('aboutMe')]
    public function aboutMe(ContactInformationsRepository $contactInformationsRepository): Response
    {
        $myContactInformation = $contactInformationsRepository->findOne('DanVraspir@protonmail.com');

        return $this->render('contact/aboutMe.html.twig', [
            'myContactInformation' => $myContactInformation,
        ]);
    }
}

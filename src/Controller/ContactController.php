<?php

namespace App\Controller;

use App\Repository\ContactInformationRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ContactController extends AbstractController
{
    #[Route('/aboutMe', name: 'app_contact_aboutMe', methods: ['GET'])]
    public function aboutMe(ContactInformationRepository $contactInformationsRepository): Response
    {
        $myContactInformation = $contactInformationsRepository->findOneById(1);

        return $this->render('contact/aboutMe.html.twig', [
            'myContactInformation' => $myContactInformation,
        ]);
    }
}

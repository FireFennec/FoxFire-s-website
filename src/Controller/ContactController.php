<?php

declare(strict_types=1);

namespace App\Controller;

use App\Repository\ContactInformationRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ContactController extends AbstractController
{
    // Display the About Me page.
    #[Route('/aboutMe', name: 'app_contact_aboutMe', methods: ['GET'])]
    public function aboutMe(ContactInformationRepository $repository): Response
    {
        // Retrieve the contact information for the current profile.
        $myContactInformation = $repository->findOneBy([
            'email' => 'DanVraspir@protonmail.com',
        ]);

        // Return HTTP 404 if the contact information does not exist.
        if ($myContactInformation === null) {
            throw $this->createNotFoundException('Contact information not found.');
        }

        return $this->render('contact/aboutMe.html.twig', [
            'myContactInformation' => $myContactInformation,
        ]);
    }
}

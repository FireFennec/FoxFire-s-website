<?php

declare(strict_types=1);

namespace App\Controller;

use App\Repository\ContactInformationRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ContactController extends AbstractController
{
    public function __construct(
        #[Autowire(env: 'APP_CONTACT_EMAIL')]
        private readonly string $contactEmail
    ) {
    }
    // Display the About Me page.
    #[Route('/aboutMe', name: 'app_contact_aboutMe', methods: ['GET'])]
    public function aboutMe(ContactInformationRepository $repository): Response
    {
        // Retrieve the configured contact profile.
        $myContactInformation = $repository->findOneBy([
            'email' => $this->contactEmail,
        ]);

        if ($myContactInformation === null) {
            throw $this->createNotFoundException('Contact information not found.');
        }

        return $this->render('contact/aboutMe.html.twig', [
            'myContactInformation' => $myContactInformation,
        ]);
    }
}

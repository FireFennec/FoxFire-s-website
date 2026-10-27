<?php

declare(strict_types=1);

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

/**
 * Handles the authenticated user's account page.
 */
class AccountController extends AbstractController
{
    /**
     * Displays the currently authenticated user's account information.
     */
    #[Route('/account', name: 'app_account', methods: ['GET'])]
    #[IsGranted('ROLE_USER')]
    public function index(): Response
    {
        return $this->render('account/index.html.twig');
    }
}

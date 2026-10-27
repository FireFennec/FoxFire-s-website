<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\User;
use App\Form\Account\ChangePasswordType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

/**
 * Handles the authenticated user's account pages.
 */
#[IsGranted('ROLE_USER')]
class AccountController extends AbstractController
{
    /**
     * Displays the currently authenticated user's account information.
     */
    #[Route('/account', name: 'app_account', methods: ['GET'])]
    public function index(): Response
    {
        return $this->render('account/index.html.twig');
    }

    /**
     * Allows the authenticated user to securely change their password.
     */
    #[Route('/account/change-password', name: 'app_account_change_password', methods: ['GET', 'POST'])]
    public function changePassword(
        Request $request,
        UserPasswordHasherInterface $passwordHasher,
        EntityManagerInterface $entityManager
    ): Response {
        $user = $this->getUser();

        if (!$user instanceof User) {
            throw $this->createAccessDeniedException();
        }

        $form = $this->createForm(ChangePasswordType::class);
        $form->handleRequest($request);

        $passwordError = null;
        $newPasswordError = null;

        if ($form->isSubmitted() && $form->isValid()) {
            $currentPassword = $form->get('currentPassword')->getData();
            $newPassword = $form->get('newPassword')->getData();

            if (!$passwordHasher->isPasswordValid($user, $currentPassword)) {
                $passwordError = 'The current password is incorrect.';
            } elseif ($passwordHasher->isPasswordValid($user, $newPassword)) {
                $newPasswordError = 'The new password must be different from the current password.';
            } else {
                $user->setPassword(
                    $passwordHasher->hashPassword($user, $newPassword)
                );

                $entityManager->flush();

                $this->addFlash(
                    'success',
                    'Your password has been changed successfully.'
                );

                return $this->redirectToRoute('app_account');
            }
        }

        return $this->render('account/changePassword.html.twig', [
            'form' => $form->createView(),
            'passwordError' => $passwordError,
            'newPasswordError' => $newPasswordError,
        ], new Response(
            status: $form->isSubmitted()
                ? Response::HTTP_UNPROCESSABLE_ENTITY
                : Response::HTTP_OK
        ));
    }
}

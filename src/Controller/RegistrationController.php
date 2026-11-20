<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\User;
use App\Form\Registration\RegistrationFormType;
use App\Repository\UserRepository;
use App\Security\EmailVerifier;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mime\Address;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use SymfonyCasts\Bundle\VerifyEmail\Exception\VerifyEmailExceptionInterface;

class RegistrationController extends AbstractController
{
    public function __construct(
        private EmailVerifier $emailVerifier
    ) {
    }

    #[Route('/register', name: 'app_register')]
    public function register(
        Request $request,
        UserPasswordHasherInterface $userPasswordHasher,
        EntityManagerInterface $entityManager
    ): Response {
        $user = new User();
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var string $plainPassword */
            $plainPassword = $form->get('plainPassword')->getData();

            $user->setPassword(
                $userPasswordHasher->hashPassword($user, $plainPassword)
            );

            $user->setTermsAcceptedAt(new \DateTimeImmutable());
            $user->setTermsAcceptedVersion('1.0');

            $entityManager->persist($user);
            $entityManager->flush();

            $this->emailVerifier->sendEmailConfirmation(
                'app_verify_email',
                $user,
                (new TemplatedEmail())
                    ->from(new Address('noreply@foxfireworld.com', "FoxFire's Website"))
                    ->to((string) $user->getEmail())
                    ->subject('Verify Your Email Address – FoxFire\'s Website')
                    ->htmlTemplate('registration/confirmation_email.html.twig')
            );

            $this->addFlash(
                'success',
                'Your account has been created successfully! Please check your inbox and follow the verification link to activate your account.'
            );

            return $this->redirectToRoute('app_mainMenu');
        }

        return $this->render('registration/register.html.twig', [
            'registrationForm' => $form,
        ]);
    }

    #[Route('/verify/email', name: 'app_verify_email')]
    public function verifyUserEmail(
        Request $request,
        UserRepository $userRepository
    ): Response {
        $id = $request->query->get('id');

        if (!is_string($id) || !ctype_digit($id)) {
            $this->addFlash(
                'verify_email_error',
                'This verification link is invalid. Please check that you opened the complete link from your email.'
            );

            return $this->redirectToRoute('app_register');
        }

        $user = $userRepository->find((int) $id);

        if (!$user) {
            $this->addFlash(
                'verify_email_error',
                'We could not find the account associated with this verification link.'
            );

            return $this->redirectToRoute('app_register');
        }

        $alreadyVerified = $user->isVerified();

        try {
            $this->emailVerifier->handleEmailConfirmation($request, $user);
        } catch (VerifyEmailExceptionInterface $exception) {
            $this->addFlash(
                'verify_email_error',
                'This verification link is invalid or has expired. Please check the verification email for the correct link.'
            );

            return $this->redirectToRoute('app_register');
        }

        if ($alreadyVerified) {
            $this->addFlash(
                'success',
                'Your email address is already verified. You can sign in to your account.'
            );
        } else {
            $this->addFlash(
                'success',
                'Your email address has been successfully verified. You can now sign in to your account.'
            );
        }

        return $this->redirectToRoute('app_mainMenu');
    }
}

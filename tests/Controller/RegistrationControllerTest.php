<?php

declare(strict_types=1);

namespace App\Tests\Controller;

use App\Entity\User;
use App\Security\EmailVerifier;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use SymfonyCasts\Bundle\VerifyEmail\VerifyEmailHelperInterface;

class RegistrationControllerTest extends WebTestCase
{
    public function testRegistrationPageLoads(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/register');

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('h1', 'Create Account');
        self::assertCount(1, $crawler->filter('input[name="registration_form[username]"]'));
        self::assertCount(1, $crawler->filter('input[name="registration_form[email]"]'));
        self::assertCount(1, $crawler->filter('input[name="registration_form[plainPassword][first]"]'));
        self::assertCount(1, $crawler->filter('input[name="registration_form[plainPassword][second]"]'));
    }

    public function testValidRegistrationPersistsUserWithHashedPasswordAndUserRole(): void
    {
        $client = static::createClient();
        $client->disableReboot();

        $username = 'test_' . bin2hex(random_bytes(6));
        $email = $username . '@example.test';
        $password = 'ValidPassword123';

        // Mock must be registered before the controller is initialized.
        $this->disableOutgoingVerificationEmail();

        $client->request('GET', '/register');

        $client->submitForm(
            'Create Account',
            $this->validFields($username, $email, $password)
        );

        self::assertResponseRedirects('/');

        $client->followRedirect();

        self::assertResponseIsSuccessful();

        self::assertSelectorTextContains(
            '.flash-notification-success',
            'Your account has been created successfully! Please check your inbox and follow the verification link to activate your account.'
        );

        $em = static::getContainer()->get(EntityManagerInterface::class);
        $em->clear();

        $user = $em->getRepository(User::class)->findOneBy([
            'email' => $email,
        ]);

        self::assertInstanceOf(User::class, $user);
        self::assertSame($username, $user->getUsername());
        self::assertSame(
            ['ROLE_USER'],
            $user->getRoles()
        );

        self::assertCount(
            0,
            $user->getAssignedRoles()
        );
        self::assertFalse($user->isVerified());
        self::assertNotSame($password, $user->getPassword());
        self::assertInstanceOf(
            \DateTimeImmutable::class,
            $user->getTermsAcceptedAt()
        );

        self::assertSame(
            '1.0',
            $user->getTermsAcceptedVersion()
        );

        self::assertTrue(
            static::getContainer()
                ->get(UserPasswordHasherInterface::class)
                ->isPasswordValid($user, $password)
        );
    }

    public function testDuplicateEmailIsRejected(): void
    {
        $client = static::createClient();
        $existing = $this->persistUser();

        $client->request('GET', '/register');

        $client->submitForm(
            'Create Account',
            $this->validFields(
                'new_' . bin2hex(random_bytes(6)),
                (string) $existing->getEmail()
            )
        );

        self::assertResponseStatusCodeSame(422);
        self::assertSelectorTextContains(
            'body',
            'This email address is already registered.'
        );
    }

    public function testDuplicateUsernameIsRejected(): void
    {
        $client = static::createClient();
        $existing = $this->persistUser();

        $client->request('GET', '/register');

        $client->submitForm(
            'Create Account',
            $this->validFields(
                (string) $existing->getUsername(),
                'another_' . bin2hex(random_bytes(6)) . '@example.test'
            )
        );

        self::assertResponseStatusCodeSame(422);
        self::assertSelectorTextContains(
            'body',
            'This username is already taken.'
        );
    }

    public function testMismatchedPasswordsAreRejected(): void
    {
        $client = static::createClient();

        $client->request('GET', '/register');

        $fields = $this->validFields(
            'test_' . bin2hex(random_bytes(6)),
            'mismatch@example.test'
        );

        $fields['registration_form[plainPassword][second]'] = 'DifferentPassword123';

        $client->submitForm('Create Account', $fields);

        self::assertResponseStatusCodeSame(422);
        self::assertSelectorTextContains(
            'body',
            'The passwords must match.'
        );
    }

    public function testWeakPasswordIsRejected(): void
    {
        $client = static::createClient();

        $client->request('GET', '/register');

        $client->submitForm(
            'Create Account',
            $this->validFields(
                'test_' . bin2hex(random_bytes(6)),
                'weak@example.test',
                'lowercaseonly'
            )
        );

        self::assertResponseStatusCodeSame(422);
        self::assertSelectorTextContains(
            'body',
            'Your password must contain at least one uppercase letter.'
        );
    }

    public function testTermsMustBeAccepted(): void
    {
        $client = static::createClient();

        $username = 'test_' . bin2hex(random_bytes(6));
        $email = $username . '@example.test';

        $client->request('GET', '/register');

        $fields = $this->validFields($username, $email);

        unset($fields['registration_form[agreeTerms]']);

        $client->submitForm('Create Account', $fields);

        self::assertResponseStatusCodeSame(422);
        self::assertSelectorTextContains(
            'body',
            'You must accept the Terms and Conditions.'
        );

        $em = static::getContainer()->get(EntityManagerInterface::class);

        $user = $em->getRepository(User::class)->findOneBy([
            'email' => $email,
        ]);

        self::assertNull($user);
    }

    public function testInvalidVerificationLinkRedirectsWithError(): void
    {
        $client = static::createClient();

        $client->request('GET', '/verify/email?id=invalid');

        self::assertResponseRedirects('/register');

        $client->followRedirect();

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains(
            '.alert-danger',
            'This verification link is invalid. Please check that you opened the complete link from your email.'
        );
    }

    public function testVerificationLinkWithNonexistentUserRedirectsWithError(): void
    {
        $client = static::createClient();

        $client->request('GET', '/verify/email?id=999999999');

        self::assertResponseRedirects('/register');

        $client->followRedirect();

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains(
            '.alert-danger',
            'We could not find the account associated with this verification link.'
        );
    }

    public function testVerificationLinkWithInvalidSignatureRedirectsWithError(): void
    {
        $client = static::createClient();

        // Create an existing user who has not verified their email.
        $user = $this->persistUser();

        self::assertFalse($user->isVerified());

        // The user ID is valid, but the verification signature is missing.
        $client->request(
            'GET',
            '/verify/email?id=' . $user->getId()
        );

        self::assertResponseRedirects('/register');

        $client->followRedirect();

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains(
            '.alert-danger',
            'This verification link is invalid or has expired.'
        );

        // Verify that the invalid link did not activate the account.
        $em = static::getContainer()->get(EntityManagerInterface::class);
        $em->clear();

        $updatedUser = $em->getRepository(User::class)->find($user->getId());

        self::assertInstanceOf(User::class, $updatedUser);
        self::assertFalse($updatedUser->isVerified());
    }
    
    public function testValidVerificationLinkVerifiesUserAndShowsSuccessMessage(): void
    {
        $client = static::createClient();

        // Create an unverified user.
        $user = $this->persistUser();

        self::assertFalse($user->isVerified());

        // Generate a valid signed verification URL.
        $verifyEmailHelper = static::getContainer()
            ->get(VerifyEmailHelperInterface::class);

        $signature = $verifyEmailHelper->generateSignature(
            'app_verify_email',
            (string) $user->getId(),
            (string) $user->getEmail(),
            ['id' => $user->getId()]
        );

        // Open the signed verification link.
        $client->request('GET', $signature->getSignedUrl());

        self::assertResponseRedirects('/');

        $client->followRedirect();

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains(
            '.flash-notification-success',
            'Your email address has been successfully verified.'
        );

        // Verify that the account is marked as verified in the database.
        $em = static::getContainer()->get(EntityManagerInterface::class);
        $em->clear();

        $verifiedUser = $em->getRepository(User::class)->find($user->getId());

        self::assertInstanceOf(User::class, $verifiedUser);
        self::assertTrue($verifiedUser->isVerified());
    }

    private function disableOutgoingVerificationEmail(): void
    {
        $verifier = $this->createMock(EmailVerifier::class);

        $verifier->expects(self::once())
            ->method('sendEmailConfirmation');

        static::getContainer()->set(EmailVerifier::class, $verifier);
    }

    /** @return array<string, string> */
    private function validFields(
        string $username,
        string $email,
        string $password = 'ValidPassword123'
    ): array {
        return [
            'registration_form[username]' => $username,
            'registration_form[email]' => $email,
            'registration_form[plainPassword][first]' => $password,
            'registration_form[plainPassword][second]' => $password,
            'registration_form[agreeTerms]' => '1',
        ];
    }

    private function persistUser(): User
    {
        $suffix = bin2hex(random_bytes(6));

        $user = (new User())
            ->setUsername('test_' . $suffix)
            ->setEmail('test_' . $suffix . '@example.test')
            ->setPassword('not-used-in-this-test');

        $em = static::getContainer()->get(EntityManagerInterface::class);

        $em->persist($user);
        $em->flush();

        return $user;
    }
}


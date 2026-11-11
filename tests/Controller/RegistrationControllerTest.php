<?php

declare(strict_types=1);

namespace App\Tests\Controller;

use App\Entity\User;
use App\Security\EmailVerifier;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

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

        $client->request('GET', '/register');

        $fields = $this->validFields(
            'test_' . bin2hex(random_bytes(6)),
            'terms@example.test'
        );

        unset($fields['registration_form[agreeTerms]']);

        $client->submitForm('Create Account', $fields);

        self::assertResponseStatusCodeSame(422);
        self::assertSelectorTextContains(
            'body',
            'You should agree to our terms.'
        );
    }

    public function testInvalidVerificationLinkReturns404(): void
    {
        $client = static::createClient();

        $client->request('GET', '/verify/email?id=invalid');

        self::assertResponseStatusCodeSame(404);
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


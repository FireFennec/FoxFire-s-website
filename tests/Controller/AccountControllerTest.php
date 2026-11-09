<?php

declare(strict_types=1);

namespace App\Tests\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AccountControllerTest extends WebTestCase
{
    private const CURRENT_PASSWORD = 'ValidPassword123';
    private const NEW_PASSWORD = 'NewPassword456';

    public function testGuestCannotAccessAccount(): void
    {
        $client = static::createClient();
        $client->request('GET', '/account');

        self::assertResponseRedirects();
        self::assertStringContainsString('/login', (string) $client->getResponse()->headers->get('Location'));
    }

    public function testGuestCannotAccessChangePassword(): void
    {
        $client = static::createClient();
        $client->request('GET', '/account/change-password');

        self::assertResponseRedirects();
        self::assertStringContainsString('/login', (string) $client->getResponse()->headers->get('Location'));
    }

    public function testAccountDisplaysAuthenticatedUserDetails(): void
    {
        $client = static::createClient();
        $user = $this->createUser();
        $client->loginUser($user);

        $client->request('GET', '/account');

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('h1', 'My Account');
        self::assertSelectorTextContains('body', (string) $user->getUsername());
        self::assertSelectorTextContains('body', (string) $user->getEmail());
        self::assertSelectorTextContains('body', 'Not verified');
        self::assertSelectorTextContains('body', 'User');
        self::assertSelectorExists('a[href="/account/change-password"]');
    }

    public function testChangePasswordPageLoads(): void
    {
        $client = static::createClient();
        $client->loginUser($this->createUser());

        $crawler = $client->request('GET', '/account/change-password');

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('h1', 'Change Password');
        self::assertCount(1, $crawler->filter('input[name="change_password[currentPassword]"]'));
        self::assertCount(1, $crawler->filter('input[name="change_password[newPassword][first]"]'));
        self::assertCount(1, $crawler->filter('input[name="change_password[newPassword][second]"]'));
    }

    public function testValidPasswordChangePersistsHashedPassword(): void
    {
        $client = static::createClient();
        $user = $this->createUser();
        $userId = $user->getId();
        $oldHash = $user->getPassword();
        $client->loginUser($user);

        $this->submitPasswordChange($client, self::CURRENT_PASSWORD, self::NEW_PASSWORD);

        self::assertResponseRedirects('/account');

        $em = static::getContainer()->get(EntityManagerInterface::class);
        $em->clear();
        $updatedUser = $em->getRepository(User::class)->find($userId);
        self::assertInstanceOf(User::class, $updatedUser);
        self::assertNotSame($oldHash, $updatedUser->getPassword());
        self::assertNotSame(self::NEW_PASSWORD, $updatedUser->getPassword());

        $hasher = static::getContainer()->get(UserPasswordHasherInterface::class);
        self::assertTrue($hasher->isPasswordValid($updatedUser, self::NEW_PASSWORD));
        self::assertFalse($hasher->isPasswordValid($updatedUser, self::CURRENT_PASSWORD));
    }

    public function testIncorrectCurrentPasswordIsRejected(): void
    {
        $client = static::createClient();
        $user = $this->createUser();
        $oldHash = $user->getPassword();
        $client->loginUser($user);

        $this->submitPasswordChange($client, 'IncorrectPassword123', self::NEW_PASSWORD);

        self::assertResponseStatusCodeSame(422);
        self::assertSelectorTextContains('body', 'The current password is incorrect.');
        $this->assertPasswordUnchanged($user, $oldHash);
    }

    public function testSamePasswordIsRejected(): void
    {
        $client = static::createClient();
        $user = $this->createUser();
        $oldHash = $user->getPassword();
        $client->loginUser($user);

        $this->submitPasswordChange($client, self::CURRENT_PASSWORD, self::CURRENT_PASSWORD);

        self::assertResponseStatusCodeSame(422);
        self::assertSelectorTextContains('body', 'The new password must be different from the current password.');
        $this->assertPasswordUnchanged($user, $oldHash);
    }

    public function testMismatchedNewPasswordsAreRejected(): void
    {
        $client = static::createClient();
        $user = $this->createUser();
        $oldHash = $user->getPassword();
        $client->loginUser($user);

        $this->submitPasswordChange($client, self::CURRENT_PASSWORD, self::NEW_PASSWORD, 'DifferentPassword789');

        self::assertResponseStatusCodeSame(422);
        self::assertSelectorTextContains('body', 'The passwords do not match.');
        $this->assertPasswordUnchanged($user, $oldHash);
    }

    public function testWeakNewPasswordIsRejected(): void
    {
        $client = static::createClient();
        $user = $this->createUser();
        $oldHash = $user->getPassword();
        $client->loginUser($user);

        $this->submitPasswordChange($client, self::CURRENT_PASSWORD, 'lowercaseonly');

        self::assertResponseStatusCodeSame(422);
        self::assertSelectorTextContains('body', 'Password must contain at least one uppercase letter.');
        $this->assertPasswordUnchanged($user, $oldHash);
    }

    public function testInvalidCsrfTokenPreventsPasswordChange(): void
    {
        $client = static::createClient();
        $user = $this->createUser();
        $oldHash = $user->getPassword();
        $client->loginUser($user);

        $client->request('GET', '/account/change-password');
        $client->request('POST', '/account/change-password', [
            'change_password' => [
                'currentPassword' => self::CURRENT_PASSWORD,
                'newPassword' => [
                    'first' => self::NEW_PASSWORD,
                    'second' => self::NEW_PASSWORD,
                ],
                '_token' => 'invalid-csrf-token',
            ],
        ]);

        self::assertResponseStatusCodeSame(422);
        self::assertSelectorTextContains('body', 'The CSRF token is invalid. Please try to resubmit the form.');
        $this->assertPasswordUnchanged($user, $oldHash);
    }

    public function testMissingCsrfTokenPreventsPasswordChange(): void
    {
        $client = static::createClient();
        $user = $this->createUser();
        $oldHash = $user->getPassword();
        $client->loginUser($user);

        $client->request('GET', '/account/change-password');
        $client->request('POST', '/account/change-password', [
            'change_password' => [
                'currentPassword' => self::CURRENT_PASSWORD,
                'newPassword' => [
                    'first' => self::NEW_PASSWORD,
                    'second' => self::NEW_PASSWORD,
                ],
            ],
        ]);

        self::assertResponseStatusCodeSame(422);
        self::assertSelectorTextContains('body', 'The CSRF token is invalid. Please try to resubmit the form.');
        $this->assertPasswordUnchanged($user, $oldHash);
    }

    private function submitPasswordChange(
        KernelBrowser $client,
        string $currentPassword,
        string $newPassword,
        ?string $confirmation = null
    ): void {
        $client->request('GET', '/account/change-password');
        $client->submitForm('Change Password', [
            'change_password[currentPassword]' => $currentPassword,
            'change_password[newPassword][first]' => $newPassword,
            'change_password[newPassword][second]' => $confirmation ?? $newPassword,
        ]);
    }

    private function assertPasswordUnchanged(User $user, ?string $oldHash): void
    {
        $em = static::getContainer()->get(EntityManagerInterface::class);
        $em->clear();
        $updatedUser = $em->getRepository(User::class)->find($user->getId());
        self::assertInstanceOf(User::class, $updatedUser);
        self::assertSame($oldHash, $updatedUser->getPassword());
    }

    private function createUser(): User
    {
        $suffix = bin2hex(random_bytes(6));
        $user = (new User())
            ->setUsername('test_' . $suffix)
            ->setEmail('test_' . $suffix . '@example.test');

        $hasher = static::getContainer()->get(UserPasswordHasherInterface::class);
        $user->setPassword($hasher->hashPassword($user, self::CURRENT_PASSWORD));

        $em = static::getContainer()->get(EntityManagerInterface::class);
        $em->persist($user);
        $em->flush();

        return $user;
    }
}

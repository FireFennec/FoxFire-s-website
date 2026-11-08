<?php

declare(strict_types=1);

namespace App\Tests\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class SecurityControllerTest extends WebTestCase
{
    public function testLoginPageLoads(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/login');
        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('h1', 'Login');
        self::assertCount(1, $crawler->filter('input[name="_username"]'));
        self::assertCount(1, $crawler->filter('input[name="_password"]'));
        self::assertCount(1, $crawler->filter('input[name="_csrf_token"]'));
    }

    public function testValidCredentialsLogUserIn(): void
    {
        $client = static::createClient();
        $user = $this->createUser();
        $this->submitLogin($client, (string) $user->getUsername(), 'ValidPassword123');

        self::assertResponseRedirects();
        $client->request('GET', '/login');
        self::assertResponseRedirects('/');
    }

    public function testWrongPasswordDoesNotLogUserIn(): void
    {
        $client = static::createClient();
        $user = $this->createUser();
        $this->submitLogin($client, (string) $user->getUsername(), 'WrongPassword123');

        $client->request('GET', '/login');
        self::assertResponseIsSuccessful();
        self::assertSelectorExists('.alert-danger');
    }

    public function testUnknownUsernameDoesNotLogUserIn(): void
    {
        $client = static::createClient();
        $this->submitLogin($client, 'missing_' . bin2hex(random_bytes(6)), 'ValidPassword123');

        $client->request('GET', '/login');
        self::assertResponseIsSuccessful();
        self::assertSelectorExists('.alert-danger');
    }

    public function testInvalidCsrfTokenPreventsLogin(): void
    {
        $client = static::createClient();
        $user = $this->createUser();
        $client->request('GET', '/login');
        $client->request('POST', '/login', [
            '_username' => $user->getUsername(),
            '_password' => 'ValidPassword123',
            '_csrf_token' => 'invalid-token',
        ]);

        $client->request('GET', '/login');
        self::assertResponseIsSuccessful();
        self::assertSelectorExists('.alert-danger');
    }

    public function testLogoutEndsAuthenticatedSession(): void
    {
        $client = static::createClient();
        $user = $this->createUser();
        $this->submitLogin($client, (string) $user->getUsername(), 'ValidPassword123');
        $client->request('GET', '/login');
        self::assertResponseRedirects('/');

        $client->request('GET', '/logout');
        self::assertResponseRedirects();
        $client->request('GET', '/login');
        self::assertResponseIsSuccessful();
    }

    private function submitLogin(\Symfony\Bundle\FrameworkBundle\KernelBrowser $client, string $username, string $password): void
    {
        $crawler = $client->request('GET', '/login');
        $token = $crawler->filter('input[name="_csrf_token"]')->attr('value');
        $client->request('POST', '/login', [
            '_username' => $username,
            '_password' => $password,
            '_csrf_token' => $token,
        ]);
    }

    private function createUser(): User
    {
        $suffix = bin2hex(random_bytes(6));
        $user = (new User())
            ->setUsername('test_' . $suffix)
            ->setEmail('test_' . $suffix . '@example.test');
        $hasher = static::getContainer()->get(UserPasswordHasherInterface::class);
        $user->setPassword($hasher->hashPassword($user, 'ValidPassword123'));
        $em = static::getContainer()->get(EntityManagerInterface::class);
        $em->persist($user);
        $em->flush();
        return $user;
    }
}

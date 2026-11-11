<?php

declare(strict_types=1);

namespace App\Tests\Controller;

use App\Entity\ContactInformation;
use App\Entity\Role;
use App\Entity\User;
use App\Repository\ContactInformationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\DomCrawler\Crawler;

class ContactControllerTest extends WebTestCase
{
    private const PROFILE_EMAIL = 'about-me-test@example.com';

    public function testAboutMePageLoadsSuccessfully(): void
    {
        $client = static::createClient();
        $this->createContact();

        $client->request('GET', '/aboutMe');

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('h1', 'About me');
        self::assertSelectorExists('section.about-section');
    }

    public function testPublicContactInformationIsVisibleToGuests(): void
    {
        $client = static::createClient();
        $this->createContact();

        $crawler = $client->request('GET', '/aboutMe');

        self::assertResponseIsSuccessful();
        $this->assertContactRow($crawler, 'Email', self::PROFILE_EMAIL);
        $this->assertContactRow($crawler, 'Telegram', 'TestTelegram');
        $this->assertContactRow($crawler, 'Discord', 'test_discord');
        $this->assertContactRow($crawler, 'Phone', '+420 123456789');
        $this->assertContactRow($crawler, 'Steam', 'TestSteam');
        $this->assertContactRow($crawler, 'Battle.net', 'TestPlayer#1234');
        $this->assertContactRow($crawler, 'GitHub', 'TestGitHub');
        $this->assertContactRow($crawler, 'Spotify', 'TestSpotify');
    }

    public function testFurryProfilesAreHiddenFromGuests(): void
    {
        $client = static::createClient();
        $this->createContact();

        $crawler = $client->request('GET', '/aboutMe');

        self::assertResponseIsSuccessful();
        self::assertNotContains('X', $this->getVisibleLabels($crawler));
        self::assertNotContains('Barq', $this->getVisibleLabels($crawler));
        self::assertCount(0, $crawler->filter('a[href="https://x.com/testprofile"]'));
        self::assertCount(0, $crawler->filter('a[href="https://barq.app/@testprofile"]'));
    }

    public function testFurryProfilesAreVisibleToFurryUser(): void
    {
        $client = static::createClient();
        $this->createContact();
        $this->loginWithRole($client, 'ROLE_FURRY');

        $crawler = $client->request('GET', '/aboutMe');

        self::assertResponseIsSuccessful();
        $this->assertContactRow($crawler, 'X', 'TestX');
        $this->assertContactRow($crawler, 'Barq', 'TestBarq');
        $this->assertExternalLink($crawler, 'https://x.com/testprofile', 'Open X profile (new tab)');
        $this->assertExternalLink($crawler, 'https://barq.app/@testprofile', 'Open Barq profile (new tab)');
    }

    public function testFurryProfilesAreHiddenFromRegularUser(): void
    {
        $client = static::createClient();
        $this->createContact();
        $this->loginWithRole($client, null);

        $crawler = $client->request('GET', '/aboutMe');

        self::assertResponseIsSuccessful();
        self::assertNotContains('X', $this->getVisibleLabels($crawler));
        self::assertNotContains('Barq', $this->getVisibleLabels($crawler));
    }

    public function testSuperAdminCanSeeFurryProfilesThroughRoleHierarchy(): void
    {
        $client = static::createClient();
        $this->createContact();
        $this->loginWithRole($client, 'ROLE_SUPER_ADMIN');

        $crawler = $client->request('GET', '/aboutMe');

        self::assertResponseIsSuccessful();
        $this->assertContactRow($crawler, 'X', 'TestX');
        $this->assertContactRow($crawler, 'Barq', 'TestBarq');
    }

    public function testAboutMeDisplaysPublicPlatformLinks(): void
    {
        $client = static::createClient();
        $this->createContact();

        $crawler = $client->request('GET', '/aboutMe');

        self::assertResponseIsSuccessful();
        $this->assertExternalLink($crawler, 'https://t.me/TestTelegram', 'Open Telegram profile (new tab)');
        $this->assertExternalLink($crawler, 'https://steamcommunity.com/id/testprofile/', 'Open Steam profile (new tab)');
        $this->assertExternalLink($crawler, 'https://github.com/testprofile', 'Open GitHub profile (new tab)');
        $this->assertExternalLink($crawler, 'https://open.spotify.com/user/testprofile', 'Open Spotify profile (new tab)');
    }

    public function testAboutMeReturns404WhenContactIsMissing(): void
    {
        $client = static::createClient();
        $this->removeExistingContact();

        $client->request('GET', '/aboutMe');

        self::assertResponseStatusCodeSame(404);
    }

    public function testAboutMeHidesEmptyContactInformation(): void
    {
        $client = static::createClient();
        $contact = $this->createContact();
        $this->loginWithRole($client, 'ROLE_FURRY');

        $contact->setTelegram(null);
        $contact->setDiscord(null);
        $contact->setX(null);
        $contact->setXLink(null);
        $contact->setSteam(null);
        $contact->setSteamLink(null);
        static::getContainer()->get(EntityManagerInterface::class)->flush();

        $crawler = $client->request('GET', '/aboutMe');

        self::assertResponseIsSuccessful();
        $labels = $this->getVisibleLabels($crawler);
        foreach (['Telegram', 'Discord', 'X', 'Steam'] as $hidden) {
            self::assertNotContains($hidden, $labels);
        }
        foreach (['Email', 'Phone', 'Barq', 'Battle.net', 'GitHub', 'Spotify'] as $visible) {
            self::assertContains($visible, $labels);
        }
        self::assertCount(0, $crawler->filter('a[href^="https://t.me/"]'));
        self::assertCount(0, $crawler->filter('a[href="https://x.com/testprofile"]'));
        self::assertCount(0, $crawler->filter('a[href="https://steamcommunity.com/id/testprofile/"]'));
    }

    public function testAboutMeDisplaysOnlyRequiredContactInformation(): void
    {
        $client = static::createClient();
        $contact = $this->createContact();

        $contact->setPhoneNumber(null);
        $contact->setDiscord(null);
        $contact->setTelegram(null);
        $contact->setX(null);
        $contact->setXLink(null);
        $contact->setBarq(null);
        $contact->setBarqLink(null);
        $contact->setSteam(null);
        $contact->setSteamLink(null);
        $contact->setBattleNet(null);
        $contact->setGithub(null);
        $contact->setGithubLink(null);
        $contact->setSpotify(null);
        $contact->setSpotifyLink(null);
        static::getContainer()->get(EntityManagerInterface::class)->flush();

        $crawler = $client->request('GET', '/aboutMe');

        self::assertResponseIsSuccessful();
        self::assertSame(['Email'], $this->getVisibleLabels($crawler));
        $this->assertContactRow($crawler, 'Email', self::PROFILE_EMAIL);
        $headings = $crawler->filter('section.about-section h2')
            ->each(static fn (Crawler $node): string => trim($node->text()));
        self::assertSame(['Contact me'], $headings);
        self::assertCount(0, $crawler->filter('a.about-link'));
    }

    public function testAboutMeDisplaysOnlyConfiguredProfile(): void
    {
        $client = static::createClient();
        $this->createContact();
        $entityManager = static::getContainer()->get(EntityManagerInterface::class);
        $repository = static::getContainer()->get(ContactInformationRepository::class);

        foreach ($repository->findBy(['email' => 'another-user@example.com']) as $existing) {
            $entityManager->remove($existing);
        }
        $entityManager->flush();

        $other = (new ContactInformation())
            ->setEmail('another-user@example.com')
            ->setDiscord('other_user_discord');
        $entityManager->persist($other);
        $entityManager->flush();

        $crawler = $client->request('GET', '/aboutMe');

        self::assertResponseIsSuccessful();
        $this->assertContactRow($crawler, 'Email', self::PROFILE_EMAIL);
        $pageText = $crawler->filter('body')->text();
        self::assertStringNotContainsString('another-user@example.com', $pageText);
        self::assertStringNotContainsString('other_user_discord', $pageText);
    }

    private function createContact(): ContactInformation
    {
        $this->removeExistingContact();
        $entityManager = static::getContainer()->get(EntityManagerInterface::class);

        $contact = (new ContactInformation())
            ->setEmail(self::PROFILE_EMAIL)
            ->setDiscord('test_discord')
            ->setTelegram('TestTelegram')
            ->setPhoneNumber('+420 123456789')
            ->setX('TestX')
            ->setXLink('https://x.com/testprofile')
            ->setBarq('TestBarq')
            ->setBarqLink('https://barq.app/@testprofile')
            ->setSteam('TestSteam')
            ->setSteamLink('https://steamcommunity.com/id/testprofile/')
            ->setBattleNet('TestPlayer#1234')
            ->setGithub('TestGitHub')
            ->setGithubLink('https://github.com/testprofile')
            ->setSpotify('TestSpotify')
            ->setSpotifyLink('https://open.spotify.com/user/testprofile');

        $entityManager->persist($contact);
        $entityManager->flush();
        return $contact;
    }

    private function removeExistingContact(): void
    {
        $entityManager = static::getContainer()->get(EntityManagerInterface::class);
        $repository = static::getContainer()->get(ContactInformationRepository::class);
        foreach ($repository->findBy(['email' => self::PROFILE_EMAIL]) as $contact) {
            $entityManager->remove($contact);
        }
        $entityManager->flush();
    }

    private function loginWithRole(KernelBrowser $client, ?string $roleName): void
    {
        $entityManager = static::getContainer()->get(EntityManagerInterface::class);
        $suffix = bin2hex(random_bytes(8));
        $user = (new User())
            ->setUsername('contact_test_' . $suffix)
            ->setEmail('contact_test_' . $suffix . '@example.test')
            ->setPassword('test-password-not-used-by-loginUser');

        if ($roleName !== null) {
            $role = $entityManager->getRepository(Role::class)->findOneBy(['name' => $roleName]);
            if ($role === null) {
                throw new \RuntimeException(sprintf('Role "%s" is missing from the test database.', $roleName));
            }
            $user->addAssignedRole($role);
        }

        $entityManager->persist($user);
        $entityManager->flush();
        $client->loginUser($user);
    }

    /** @return list<string> */
    private function getVisibleLabels(Crawler $crawler): array
    {
        return $crawler->filter('.about-label')
            ->each(static fn (Crawler $node): string => trim($node->text()));
    }

    private function assertContactRow(Crawler $crawler, string $label, string $value): void
    {
        $rows = $crawler->filter('.about-row')->reduce(
            static fn (Crawler $row): bool => trim($row->filter('.about-label')->text()) === $label
        );
        self::assertCount(1, $rows, sprintf('Expected exactly one row for "%s".', $label));
        self::assertSame($value, trim($rows->filter('.about-value')->text()));
    }

    private function assertExternalLink(Crawler $crawler, string $url, string $accessibleName): void
    {
        $links = $crawler->filter('a.about-link')->reduce(
            static fn (Crawler $link): bool => $link->attr('aria-label') === $accessibleName
        );
        self::assertCount(1, $links, sprintf('Expected one link named "%s".', $accessibleName));
        self::assertSame($url, $links->attr('href'));
        self::assertSame('_blank', $links->attr('target'));
        $rel = preg_split('/\s+/', trim((string) $links->attr('rel')));
        self::assertIsArray($rel);
        self::assertContains('noopener', $rel);
        self::assertContains('noreferrer', $rel);
    }
}

<?php

declare(strict_types=1);

namespace App\Tests\Controller;

use App\Entity\ContactInformation;
use App\Repository\ContactInformationRepository;
use Doctrine\ORM\EntityManagerInterface;
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

    public function testAboutMeDisplaysContactInformation(): void
    {
        $client = static::createClient();

        $this->createContact();

        $crawler = $client->request('GET', '/aboutMe');

        self::assertResponseIsSuccessful();

        // Verify that each contact value belongs to the correct row.
        $this->assertContactRow($crawler, 'Email', self::PROFILE_EMAIL);
        $this->assertContactRow($crawler, 'Telegram', 'TestTelegram');
        $this->assertContactRow($crawler, 'Discord', 'test_discord');
        $this->assertContactRow($crawler, 'Phone', '+420 123456789');
        $this->assertContactRow($crawler, 'X', 'TestX');
        $this->assertContactRow($crawler, 'Barq', 'TestBarq');
        $this->assertContactRow($crawler, 'Steam', 'TestSteam');
        $this->assertContactRow($crawler, 'Battle.net', 'TestPlayer#1234');
    }

    public function testAboutMeDisplaysPlatformLinks(): void
    {
        $client = static::createClient();

        $this->createContact();

        $crawler = $client->request('GET', '/aboutMe');

        self::assertResponseIsSuccessful();

        // Verify URLs, accessible names and security attributes.
        $this->assertExternalLink(
            $crawler,
            'https://t.me/TestTelegram',
            'Open Telegram profile (new tab)'
        );

        $this->assertExternalLink(
            $crawler,
            'https://x.com/testprofile',
            'Open X profile (new tab)'
        );

        $this->assertExternalLink(
            $crawler,
            'https://barq.app/@testprofile',
            'Open Barq profile (new tab)'
        );

        $this->assertExternalLink(
            $crawler,
            'https://steamcommunity.com/id/testprofile/',
            'Open Steam profile (new tab)'
        );
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

        $entityManager = static::getContainer()->get(EntityManagerInterface::class);

        // Remove selected optional contact information.
        $contact->setTelegram(null);
        $contact->setDiscord(null);
        $contact->setX(null);
        $contact->setXLink(null);
        $contact->setSteam(null);
        $contact->setSteamLink(null);

        $entityManager->flush();

        $crawler = $client->request('GET', '/aboutMe');

        self::assertResponseIsSuccessful();

        $visibleLabels = $this->getVisibleLabels($crawler);

        // Verify that empty contact rows are hidden.
        self::assertNotContains('Telegram', $visibleLabels);
        self::assertNotContains('Discord', $visibleLabels);
        self::assertNotContains('X', $visibleLabels);
        self::assertNotContains('Steam', $visibleLabels);

        // Verify that populated contact rows remain visible.
        self::assertContains('Email', $visibleLabels);
        self::assertContains('Phone', $visibleLabels);
        self::assertContains('Barq', $visibleLabels);
        self::assertContains('Battle.net', $visibleLabels);

        // Verify that links for missing profiles are not rendered.
        self::assertCount(
            0,
            $crawler->filter('a[href^="https://t.me/"]')
        );

        self::assertCount(
            0,
            $crawler->filter('a[href="https://x.com/testprofile"]')
        );

        self::assertCount(
            0,
            $crawler->filter('a[href="https://steamcommunity.com/id/testprofile/"]')
        );
    }

    public function testAboutMeDisplaysOnlyRequiredContactInformation(): void
    {
        $client = static::createClient();

        $contact = $this->createContact();

        $entityManager = static::getContainer()->get(EntityManagerInterface::class);

        // Remove all optional contact information.
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

        $entityManager->flush();

        $crawler = $client->request('GET', '/aboutMe');

        self::assertResponseIsSuccessful();

        // Only the required email row should be displayed.
        self::assertSame(
            ['Email'],
            $this->getVisibleLabels($crawler)
        );

        $this->assertContactRow(
            $crawler,
            'Email',
            self::PROFILE_EMAIL
        );

        // Empty sections must not be rendered.
        $sectionHeadings = $crawler
            ->filter('section.about-section h2')
            ->each(static fn (Crawler $node): string => trim($node->text()));

        self::assertSame(['Contact me'], $sectionHeadings);

        // No external profile links should exist.
        self::assertCount(
            0,
            $crawler->filter('a.about-link')
        );
    }

    public function testAboutMeDisplaysOnlyConfiguredProfile(): void
    {
        $client = static::createClient();

        $this->createContact();

        $entityManager = static::getContainer()->get(EntityManagerInterface::class);
        $repository = static::getContainer()->get(ContactInformationRepository::class);

        // Ensure that the unrelated test profile does not already exist.
        foreach ($repository->findBy([
            'email' => 'another-user@example.com',
        ]) as $existingContact) {
            $entityManager->remove($existingContact);
        }

        $entityManager->flush();

        // Create another profile that must not be displayed.
        $otherContact = new ContactInformation();
        $otherContact->setEmail('another-user@example.com');
        $otherContact->setDiscord('other_user_discord');

        $entityManager->persist($otherContact);
        $entityManager->flush();

        $crawler = $client->request('GET', '/aboutMe');

        self::assertResponseIsSuccessful();

        // The configured profile must be displayed.
        $this->assertContactRow(
            $crawler,
            'Email',
            self::PROFILE_EMAIL
        );

        // Information belonging to another profile must remain hidden.
        $pageText = $crawler->filter('body')->text();

        self::assertStringNotContainsString(
            'another-user@example.com',
            $pageText
        );

        self::assertStringNotContainsString(
            'other_user_discord',
            $pageText
        );
    }

    private function createContact(): ContactInformation
    {
        $this->removeExistingContact();

        $entityManager = static::getContainer()->get(EntityManagerInterface::class);

        // Create predictable contact information for testing.
        $contact = new ContactInformation();

        $contact->setEmail(self::PROFILE_EMAIL);
        $contact->setDiscord('test_discord');
        $contact->setTelegram('TestTelegram');
        $contact->setPhoneNumber('+420 123456789');
        $contact->setX('TestX');
        $contact->setXLink('https://x.com/testprofile');
        $contact->setBarq('TestBarq');
        $contact->setBarqLink('https://barq.app/@testprofile');
        $contact->setSteam('TestSteam');
        $contact->setSteamLink('https://steamcommunity.com/id/testprofile/');
        $contact->setBattleNet('TestPlayer#1234');

        $entityManager->persist($contact);
        $entityManager->flush();

        return $contact;
    }

    private function removeExistingContact(): void
    {
        $entityManager = static::getContainer()->get(EntityManagerInterface::class);
        $repository = static::getContainer()->get(ContactInformationRepository::class);

        // Remove the configured test profile if it already exists.
        foreach ($repository->findBy([
            'email' => self::PROFILE_EMAIL,
        ]) as $contact) {
            $entityManager->remove($contact);
        }

        $entityManager->flush();
    }

    private function getVisibleLabels(Crawler $crawler): array
    {
        // Return the exact labels of all visible contact rows.
        return $crawler
            ->filter('.about-label')
            ->each(static fn (Crawler $node): string => trim($node->text()));
    }

    private function assertContactRow(
        Crawler $crawler,
        string $label,
        string $value
    ): void {
        // Find the row with the exact expected label.
        $rows = $crawler->filter('.about-row')->reduce(
            static function (Crawler $row) use ($label): bool {
                return trim($row->filter('.about-label')->text()) === $label;
            }
        );

        self::assertCount(
            1,
            $rows,
            sprintf('Expected exactly one row for "%s".', $label)
        );

        // Verify that the row contains the expected value.
        self::assertSame(
            $value,
            trim($rows->filter('.about-value')->text())
        );
    }

    private function assertExternalLink(
        Crawler $crawler,
        string $url,
        string $accessibleName
    ): void {
        // Find the link by its accessible name.
        $links = $crawler->filter('a.about-link')->reduce(
            static fn (Crawler $link): bool =>
                $link->attr('aria-label') === $accessibleName
        );

        self::assertCount(
            1,
            $links,
            sprintf('Expected one link named "%s".', $accessibleName)
        );

        // Verify the destination and new-tab behavior.
        self::assertSame($url, $links->attr('href'));
        self::assertSame('_blank', $links->attr('target'));

        // Verify that both security attributes are present.
        $rel = preg_split('/\s+/', trim((string) $links->attr('rel')));

        self::assertContains('noopener', $rel);
        self::assertContains('noreferrer', $rel);
    }
}

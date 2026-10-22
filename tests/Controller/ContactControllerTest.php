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

        self::assertNotContains('Telegram', $visibleLabels);
        self::assertNotContains('Discord', $visibleLabels);
        self::assertNotContains('X', $visibleLabels);
        self::assertNotContains('Steam', $visibleLabels);

        self::assertContains('Email', $visibleLabels);
        self::assertContains('Phone', $visibleLabels);
        self::assertContains('Barq', $visibleLabels);
        self::assertContains('Battle.net', $visibleLabels);

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

        self::assertSame(
            ['Email'],
            $this->getVisibleLabels($crawler)
        );

        $this->assertContactRow(
            $crawler,
            'Email',
            self::PROFILE_EMAIL
        );

        $sectionHeadings = $crawler
            ->filter('section.about-section h2')
            ->each(static fn (Crawler $node): string => trim($node->text()));

        self::assertSame(['Contact me'], $sectionHeadings);

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

        foreach ($repository->findBy([
            'email' => 'another-user@example.com',
        ]) as $existingContact) {
            $entityManager->remove($existingContact);
        }

        $entityManager->flush();

        $otherContact = new ContactInformation();
        $otherContact->setEmail('another-user@example.com');
        $otherContact->setDiscord('other_user_discord');

        $entityManager->persist($otherContact);
        $entityManager->flush();

        $crawler = $client->request('GET', '/aboutMe');

        self::assertResponseIsSuccessful();

        $this->assertContactRow(
            $crawler,
            'Email',
            self::PROFILE_EMAIL
        );

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

        foreach ($repository->findBy([
            'email' => self::PROFILE_EMAIL,
        ]) as $contact) {
            $entityManager->remove($contact);
        }

        $entityManager->flush();
    }

    /**
     * @return list<string>
     */
    private function getVisibleLabels(Crawler $crawler): array
    {
        return $crawler
            ->filter('.about-label')
            ->each(static fn (Crawler $node): string => trim($node->text()));
    }

    private function assertContactRow(
        Crawler $crawler,
        string $label,
        string $value
    ): void {
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
        $links = $crawler->filter('a.about-link')->reduce(
            static fn (Crawler $link): bool =>
                $link->attr('aria-label') === $accessibleName
        );

        self::assertCount(
            1,
            $links,
            sprintf('Expected one link named "%s".', $accessibleName)
        );

        self::assertSame($url, $links->attr('href'));
        self::assertSame('_blank', $links->attr('target'));

        $rel = preg_split('/\s+/', trim((string) $links->attr('rel')));

        self::assertContains('noopener', $rel);
        self::assertContains('noreferrer', $rel);
    }
}

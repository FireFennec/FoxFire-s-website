<?php

declare(strict_types=1);

namespace App\Tests\Controller;

use App\Entity\ContactInformation;
use App\Repository\ContactInformationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ContactControllerTest extends WebTestCase
{
    private const PROFILE_EMAIL = 'DanVraspir@protonmail.com';

    public function testAboutMePageLoadsSuccessfully(): void
    {
        $client = static::createClient();

        $this->createContact();

        $client->request('GET', '/aboutMe');

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('h1', 'About me');
    }

    public function testAboutMeDisplaysContactInformation(): void
    {
        $client = static::createClient();

        $this->createContact();

        $client->request('GET', '/aboutMe');

        self::assertResponseIsSuccessful();

        // Verify that the expected contact information is displayed.
        self::assertSelectorTextContains('body', 'test_discord');
        self::assertSelectorTextContains('body', 'TestTelegram');
        self::assertSelectorTextContains('body', 'TestSteam');
        self::assertSelectorTextContains('body', 'TestPlayer#1234');
    }

    public function testAboutMeDisplaysPlatformLinks(): void
    {
        $client = static::createClient();

        $this->createContact();

        $crawler = $client->request('GET', '/aboutMe');

        self::assertResponseIsSuccessful();

        // Verify that external links point to the correct platforms.
        self::assertCount(
            1,
            $crawler->filter('a[href="https://steamcommunity.com/id/testprofile/"]')
        );

        self::assertCount(
            1,
            $crawler->filter('a[href="https://x.com/testprofile"]')
        );

        self::assertCount(
            1,
            $crawler->filter('a[href="https://barq.app/@testprofile"]')
        );

        self::assertCount(
            1,
            $crawler->filter('a[href="https://t.me/TestTelegram"]')
        );
    }

    public function testAboutMeReturns404WhenContactIsMissing(): void
    {
        $client = static::createClient();

        $entityManager = static::getContainer()->get(EntityManagerInterface::class);
        $repository = static::getContainer()->get(ContactInformationRepository::class);

        // Remove the profile expected by the About Me controller.
        foreach ($repository->findBy([
            'email' => self::PROFILE_EMAIL,
        ]) as $contact) {
            $entityManager->remove($contact);
        }

        $entityManager->flush();

        $client->request('GET', '/aboutMe');

        self::assertResponseStatusCodeSame(404);
    }

    public function testAboutMeHidesEmptyContactInformation(): void
    {
        $client = static::createClient();

        // Create a contact with some optional fields missing.
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

        // Compare exact labels to avoid partial matches such as "X".
        $visibleLabels = $crawler
            ->filter('.about-label')
            ->each(static fn ($node): string => trim($node->text()));

        self::assertNotContains('Telegram', $visibleLabels);
        self::assertNotContains('Discord', $visibleLabels);
        self::assertNotContains('X', $visibleLabels);
        self::assertNotContains('Steam', $visibleLabels);

        // Verify that the Telegram link is not generated.
        self::assertCount(
            0,
            $crawler->filter('a[href^="https://t.me/"]')
        );

        // Verify that populated contact information remains visible.
        self::assertContains('Barq', $visibleLabels);
        self::assertContains('Battle.net', $visibleLabels);

        self::assertSelectorTextContains('body', 'TestBarq');
        self::assertSelectorTextContains('body', 'TestPlayer#1234');
    }

    public function testAboutMeDisplaysOnlyRequiredContactInformation(): void
    {
        $client = static::createClient();

        $contact = $this->createContact();

        $entityManager = static::getContainer()->get(EntityManagerInterface::class);

        // Remove all optional values while keeping the required email.
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

        // The email must remain visible.
        self::assertSelectorTextContains('body', self::PROFILE_EMAIL);

        // Only the email contact row should be rendered.
        $visibleLabels = $crawler
            ->filter('.about-label')
            ->each(static fn ($node): string => trim($node->text()));

        self::assertSame(['Email'], $visibleLabels);

        // No external platform links should be displayed.
        self::assertCount(
            0,
            $crawler->filter('a.about-link')
        );
    }

    private function createContact(): ContactInformation
    {
        $entityManager = static::getContainer()->get(EntityManagerInterface::class);
        $repository = static::getContainer()->get(ContactInformationRepository::class);

        // Ensure that each test uses predictable contact data.
        foreach ($repository->findBy([
            'email' => self::PROFILE_EMAIL,
        ]) as $existingContact) {
            $entityManager->remove($existingContact);
        }

        $entityManager->flush();

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
}

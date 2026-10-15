<?php

declare(strict_types=1);

namespace App\Tests\Controller;

use App\Entity\ContactInformation;
use App\Repository\ContactInformationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ContactControllerTest extends WebTestCase
{
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

        // Verify that contact information is displayed.
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

        // Verify that the external links point to the correct URLs.
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

        // Remove the profile used by the About Me controller.
        $contacts = $repository->findBy([
            'email' => 'DanVraspir@protonmail.com',
        ]);

        foreach ($contacts as $contact) {
            $entityManager->remove($contact);
        }

        $entityManager->flush();

        $client->request('GET', '/aboutMe');

        self::assertResponseStatusCodeSame(404);
    }

    private function createContact(): ContactInformation
    {
        $entityManager = static::getContainer()->get(EntityManagerInterface::class);
        $repository = static::getContainer()->get(ContactInformationRepository::class);

        // Ensure the test uses its own predictable contact data.
        foreach ($repository->findBy([
            'email' => 'DanVraspir@protonmail.com',
        ]) as $existingContact) {
            $entityManager->remove($existingContact);
        }

        $entityManager->flush();

        $contact = new ContactInformation();

        $contact->setEmail('DanVraspir@protonmail.com');
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

        // Load the About Me page.
        $crawler = $client->request('GET', '/aboutMe');

        self::assertResponseIsSuccessful();

        // Verify that empty contact rows are not displayed.
        self::assertCount(0, $crawler->filter('.about-label:contains("Telegram")'));
        self::assertCount(0, $crawler->filter('.about-label:contains("Discord")'));
        self::assertCount(0, $crawler->filter('.about-label:contains("X")'));
        self::assertCount(0, $crawler->filter('.about-label:contains("Steam")'));

        // Verify that the Telegram link is not generated.
        self::assertCount(0, $crawler->filter('a[href^="https://t.me/"]'));

        // Verify that populated contact information remains visible.
        self::assertSelectorTextContains('body', 'TestBarq');
        self::assertSelectorTextContains('body', 'TestPlayer#1234');
    }
}

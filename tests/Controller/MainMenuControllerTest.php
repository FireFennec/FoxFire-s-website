<?php

declare(strict_types=1);

namespace App\Tests\Controller;

use App\Entity\WebsiteFeature;
use App\Model\FeaturePriorityEnum;
use App\Model\FeaturesStatusEnum;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class MainMenuControllerTest extends WebTestCase
{
    public function testHomepageLoadsSuccessfully(): void
    {
        $client = static::createClient();

        $client->request('GET', '/');

        self::assertResponseIsSuccessful();
    }

    public function testFeaturesAreGroupedByStatus(): void
    {
        $client = static::createClient();

        $entityManager = static::getContainer()->get(EntityManagerInterface::class);

        $feature = new WebsiteFeature();

        $feature->setName('Test New Feature');
        $feature->setDescription('Feature created for PHPUnit testing.');
        $feature->setStatus(FeaturesStatusEnum::NEW_FEATURE);
        $feature->setPriority(FeaturePriorityEnum::SOON);

        $updateFeature = new WebsiteFeature();

        $updateFeature->setName('Test Update Feature');
        $updateFeature->setDescription('Feature planned for an update.');
        $updateFeature->setStatus(FeaturesStatusEnum::UPDATE_FEATURE);
        $updateFeature->setPriority(FeaturePriorityEnum::ANYTIME);

        $completedFeature = new WebsiteFeature();

        $completedFeature->setName('Test Completed Feature');
        $completedFeature->setDescription('Feature already completed.');
        $completedFeature->setStatus(FeaturesStatusEnum::COMPLETED_FEATURE);
        $completedFeature->setPriority(FeaturePriorityEnum::ASAP);

        $entityManager->persist($feature);
        $entityManager->persist($updateFeature);
        $entityManager->persist($completedFeature);
        $entityManager->flush();

        $crawler = $client->request('GET', '/');

        self::assertResponseIsSuccessful();

        $sections = $crawler->filter('section.homepage-feature-section');

        self::assertCount(3, $sections);

        self::assertSame(
            'The plan of things I want to add',
            $sections->eq(0)->filter('h2')->text()
        );

        self::assertSame(
            'The plan of things I want to update',
            $sections->eq(1)->filter('h2')->text()
        );

        self::assertSame(
            'The list of existing features',
            $sections->eq(2)->filter('h2')->text()
        );

        self::assertStringContainsString(
            'Test New Feature',
            $sections->eq(0)->text()
        );

        self::assertStringContainsString(
            'Test Update Feature',
            $sections->eq(1)->text()
        );

        self::assertStringContainsString(
            'Test Completed Feature',
            $sections->eq(2)->text()
        );

        self::assertStringNotContainsString(
            'Test Update Feature',
            $sections->eq(0)->text()
        );

        self::assertStringNotContainsString(
            'Test Completed Feature',
            $sections->eq(0)->text()
        );

        self::assertStringNotContainsString(
            'Test New Feature',
            $sections->eq(1)->text()
        );

        self::assertStringNotContainsString(
            'Test Completed Feature',
            $sections->eq(1)->text()
        );

        self::assertStringNotContainsString(
            'Test New Feature',
            $sections->eq(2)->text()
        );

        self::assertStringNotContainsString(
            'Test Update Feature',
            $sections->eq(2)->text()
        );
    }
}

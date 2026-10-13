<?php

declare(strict_types=1);

namespace App\Tests\Controller;

use App\Entity\WebsiteFeature;
use App\Model\FeaturePriorityEnum;
use App\Model\FeaturesStatusEnum;
use App\Repository\WebsiteFeatureRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class FeaturesListControllerTest extends WebTestCase
{
    public function testFeatureListLoadsSuccessfully(): void
    {
        $client = static::createClient();

        $client->request('GET', '/feature/list');

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains(
            'section.features-section:nth-of-type(1) h2',
            'To Do Features'
        );
        self::assertSelectorTextContains(
            'section.features-section:nth-of-type(2) h2',
            'Completed Features'
        );
    }

    public function testFeaturesAreSeparatedByStatus(): void
    {
        $client = static::createClient();
        $entityManager = static::getContainer()->get(EntityManagerInterface::class);

        // Create features with three different statuses.
        $this->createFeature(
            $entityManager,
            'Test New Feature',
            'A new planned feature.',
            FeaturesStatusEnum::NEW_FEATURE,
            FeaturePriorityEnum::SOON
        );
        $this->createFeature(
            $entityManager,
            'Test Update Feature',
            'A feature planned for an update.',
            FeaturesStatusEnum::UPDATE_FEATURE,
            FeaturePriorityEnum::ANYTIME
        );
        $this->createFeature(
            $entityManager,
            'Test Completed Feature',
            'A completed feature.',
            FeaturesStatusEnum::COMPLETED_FEATURE,
            FeaturePriorityEnum::ASAP
        );

        $crawler = $client->request('GET', '/feature/list');
        self::assertResponseIsSuccessful();

        $sections = $crawler->filter('section.features-section');
        self::assertCount(2, $sections);

        $toDoSection = $sections->eq(0);
        $completedSection = $sections->eq(1);

        self::assertSame('To Do Features', $toDoSection->filter('h2')->text());
        self::assertSame('Completed Features', $completedSection->filter('h2')->text());

        // Unfinished features belong in the To Do section.
        self::assertStringContainsString('Test New Feature', $toDoSection->text());
        self::assertStringContainsString('Test Update Feature', $toDoSection->text());
        self::assertStringNotContainsString('Test Completed Feature', $toDoSection->text());

        // Completed features must not appear in the To Do section.
        self::assertStringContainsString('Test Completed Feature', $completedSection->text());
        self::assertStringNotContainsString('Test New Feature', $completedSection->text());
        self::assertStringNotContainsString('Test Update Feature', $completedSection->text());
    }

    public function testFeatureDetailDisplaysCorrectly(): void
    {
        $client = static::createClient();
        $entityManager = static::getContainer()->get(EntityManagerInterface::class);

        $feature = $this->createFeature(
            $entityManager,
            'Test Feature Detail',
            'Description for testing feature details.',
            FeaturesStatusEnum::NEW_FEATURE,
            FeaturePriorityEnum::HIGH_PRIORITY
        );

        $featureId = $feature->getId();
        self::assertNotNull($featureId);

        $client->request('GET', '/feature/' . $featureId);

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('body', 'Test Feature Detail');
        self::assertSelectorTextContains('body', 'Description for testing feature details.');
    }

    public function testNonExistentFeatureReturns404(): void
    {
        $client = static::createClient();

        $client->request('GET', '/feature/999999999');

        self::assertResponseStatusCodeSame(404);
    }

    public function testFeatureCanBeCreated(): void
    {
        $client = static::createClient();

        $client->request('GET', '/feature/createFeature');
        self::assertResponseIsSuccessful();

        // Submit the creation form with valid values.
        $client->submitForm('Create Feature', [
            'create_feature_form[name]' => 'Test Created Feature',
            'create_feature_form[description]' => 'Feature created through a functional test.',
            'create_feature_form[status]' => FeaturesStatusEnum::NEW_FEATURE->value,
            'create_feature_form[priority]' => (string) FeaturePriorityEnum::SOON->value,
        ]);

        self::assertResponseRedirects();
        $client->followRedirect();
        self::assertResponseIsSuccessful();

        self::assertSelectorTextContains('body', 'Test Created Feature');
        self::assertSelectorTextContains('body', 'Feature created through a functional test.');

        // The redirect points to /feature/{id}.
        $featureId = (int) basename($client->getRequest()->getPathInfo());
        self::assertGreaterThan(0, $featureId);

        // Detach managed entities before verifying the stored values.
        $entityManager = static::getContainer()->get(EntityManagerInterface::class);
        $entityManager->clear();

        $repository = static::getContainer()->get(WebsiteFeatureRepository::class);
        $savedFeature = $repository->findOne($featureId);

        self::assertNotNull($savedFeature);
        self::assertSame('Test Created Feature', $savedFeature->getName());
        self::assertSame('Feature created through a functional test.', $savedFeature->getDescription());
        self::assertSame(FeaturesStatusEnum::NEW_FEATURE, $savedFeature->getStatus());
        self::assertSame(FeaturePriorityEnum::SOON, $savedFeature->getPriority());
    }

    public function testFeatureCanBeEdited(): void
    {
        $client = static::createClient();
        $entityManager = static::getContainer()->get(EntityManagerInterface::class);

        $feature = $this->createFeature(
            $entityManager,
            'Original Feature',
            'Original description.',
            FeaturesStatusEnum::NEW_FEATURE,
            FeaturePriorityEnum::ANYTIME
        );

        $featureId = $feature->getId();
        self::assertNotNull($featureId);

        $client->request('GET', '/feature/editFeature/' . $featureId);
        self::assertResponseIsSuccessful();

        // Update the existing feature through its form.
        $client->submitForm('Save Changes', [
            'create_feature_form[name]' => 'Updated Feature',
            'create_feature_form[description]' => 'Updated description.',
            'create_feature_form[status]' => FeaturesStatusEnum::COMPLETED_FEATURE->value,
            'create_feature_form[priority]' => (string) FeaturePriorityEnum::ASAP->value,
        ]);

        self::assertResponseRedirects('/feature/' . $featureId);
        $client->followRedirect();
        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('body', 'Updated Feature');

        // Read fresh values from the database.
        $entityManager->clear();
        $repository = static::getContainer()->get(WebsiteFeatureRepository::class);
        $updatedFeature = $repository->findOne($featureId);

        self::assertNotNull($updatedFeature);
        self::assertSame('Updated Feature', $updatedFeature->getName());
        self::assertSame('Updated description.', $updatedFeature->getDescription());
        self::assertSame(FeaturesStatusEnum::COMPLETED_FEATURE, $updatedFeature->getStatus());
        self::assertSame(FeaturePriorityEnum::ASAP, $updatedFeature->getPriority());
    }

    public function testFeatureCanBeDeleted(): void
    {
        $client = static::createClient();
        $entityManager = static::getContainer()->get(EntityManagerInterface::class);

        $feature = $this->createFeature(
            $entityManager,
            'Test Feature To Delete',
            'Feature created for deletion testing.',
            FeaturesStatusEnum::NEW_FEATURE,
            FeaturePriorityEnum::SOON
        );

        $featureId = $feature->getId();
        self::assertNotNull($featureId);

        $crawler = $client->request('GET', '/feature/' . $featureId);
        self::assertResponseIsSuccessful();

        // Submit the actual delete form, including its CSRF token.
        $deleteForm = $crawler->filter(
            'form[action="/feature/deleteFeature/' . $featureId . '"]'
        )->form();
        $client->submit($deleteForm);

        self::assertResponseRedirects('/feature/list');

        $entityManager->clear();
        $repository = static::getContainer()->get(WebsiteFeatureRepository::class);
        self::assertNull($repository->findOne($featureId));
    }

    public function testDeleteWithInvalidCsrfTokenIsRejected(): void
    {
        $client = static::createClient();
        $entityManager = static::getContainer()->get(EntityManagerInterface::class);

        $feature = $this->createFeature(
            $entityManager,
            'Test Protected Feature',
            'Feature protected against invalid CSRF tokens.',
            FeaturesStatusEnum::NEW_FEATURE,
            FeaturePriorityEnum::SOON
        );

        $featureId = $feature->getId();
        self::assertNotNull($featureId);

        // Invalid CSRF tokens must never delete records.
        $client->request(
            'POST',
            '/feature/deleteFeature/' . $featureId,
            ['_token' => 'invalid_csrf_token']
        );

        self::assertResponseStatusCodeSame(403);

        $entityManager->clear();
        $repository = static::getContainer()->get(WebsiteFeatureRepository::class);
        self::assertNotNull($repository->findOne($featureId));
    }

    public function testEditingNonExistentFeatureReturns404(): void
    {
        $client = static::createClient();

        $client->request('GET', '/feature/editFeature/999999999');

        self::assertResponseStatusCodeSame(404);
    }

    public function testDeletingNonExistentFeatureReturns404(): void
    {
        $client = static::createClient();

        // The controller checks whether the feature exists before checking CSRF.
        $client->request('POST', '/feature/deleteFeature/999999999');

        self::assertResponseStatusCodeSame(404);
    }

    private function createFeature(
        EntityManagerInterface $entityManager,
        string $name,
        string $description,
        FeaturesStatusEnum $status,
        FeaturePriorityEnum $priority
    ): WebsiteFeature {
        $feature = new WebsiteFeature();
        $feature->setName($name);
        $feature->setDescription($description);
        $feature->setStatus($status);
        $feature->setPriority($priority);

        $entityManager->persist($feature);
        $entityManager->flush();

        return $feature;
    }
}

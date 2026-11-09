<?php

declare(strict_types=1);

namespace App\Tests\Controller;

use App\Entity\WebsiteFeature;
use App\Entity\User;
use App\Model\FeaturePriorityEnum;
use App\Model\FeaturesStatusEnum;
use App\Repository\WebsiteFeatureRepository;
use Doctrine\ORM\EntityManagerInterface;
use DateTimeImmutable;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;

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

        self::assertStringContainsString('Test New Feature', $toDoSection->text());
        self::assertStringContainsString('Test Update Feature', $toDoSection->text());
        self::assertStringNotContainsString('Test Completed Feature', $toDoSection->text());

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
        $this->loginAsAdmin($client);

        $client->request('GET', '/feature/createFeature');
        self::assertResponseIsSuccessful();

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

        $featureId = (int) basename($client->getRequest()->getPathInfo());
        self::assertGreaterThan(0, $featureId);

        // Clear the identity map to verify values persisted in the database.
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
        $this->loginAsAdmin($client);
        $entityManager = static::getContainer()->get(EntityManagerInterface::class);

        $feature = $this->createFeature(
            $entityManager,
            'Original Feature',
            'Original description.',
            FeaturesStatusEnum::NEW_FEATURE,
            FeaturePriorityEnum::ANYTIME
        );

        $feature->setDeadlineAt(new DateTimeImmutable('2027-01-15'));
        $entityManager->flush();

        $featureId = $feature->getId();
        self::assertNotNull($featureId);

        $client->request('GET', '/feature/editFeature/' . $featureId);
        self::assertResponseIsSuccessful();

        $client->submitForm('Save Changes', [
            'create_feature_form[name]' => 'Updated Feature',
            'create_feature_form[description]' => 'Updated description.',
            'create_feature_form[status]' => FeaturesStatusEnum::COMPLETED_FEATURE->value,
            'create_feature_form[priority]' => (string) FeaturePriorityEnum::ASAP->value,
            'create_feature_form[deadlineAt]' => '2027-06-15',
        ]);

        self::assertResponseRedirects('/feature/' . $featureId);
        $client->followRedirect();
        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('body', 'Updated Feature');

        // Clear managed entities before checking the persisted changes.
        $entityManager = static::getContainer()->get(EntityManagerInterface::class);
        $entityManager->clear();
        $repository = static::getContainer()->get(WebsiteFeatureRepository::class);
        $updatedFeature = $repository->findOne($featureId);

        self::assertNotNull($updatedFeature);
        self::assertSame('Updated Feature', $updatedFeature->getName());
        self::assertSame('Updated description.', $updatedFeature->getDescription());
        self::assertSame(FeaturesStatusEnum::COMPLETED_FEATURE, $updatedFeature->getStatus());
        self::assertSame(FeaturePriorityEnum::ASAP, $updatedFeature->getPriority());
        self::assertSame('2027-06-15', $updatedFeature->getDeadlineAt()?->format('Y-m-d'));

        $client->request('GET', '/feature/editFeature/' . $featureId);
        self::assertResponseIsSuccessful();

        $client->submitForm('Save Changes', [
            'create_feature_form[name]' => 'Updated Feature',
            'create_feature_form[description]' => 'Updated description.',
            'create_feature_form[status]' => FeaturesStatusEnum::COMPLETED_FEATURE->value,
            'create_feature_form[priority]' => (string) FeaturePriorityEnum::ASAP->value,
            'create_feature_form[deadlineAt]' => '',
        ]);

        self::assertResponseRedirects('/feature/' . $featureId);

        $entityManager = static::getContainer()->get(EntityManagerInterface::class);
        $entityManager->clear();
        $repository = static::getContainer()->get(WebsiteFeatureRepository::class);
        $featureWithoutDeadline = $repository->findOne($featureId);

        self::assertNotNull($featureWithoutDeadline);
        self::assertNull($featureWithoutDeadline->getDeadlineAt());
    }

    public function testFeatureCanBeDeleted(): void
    {
        $client = static::createClient();
        $this->loginAsAdmin($client);
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
        $this->loginAsAdmin($client);
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
        $this->loginAsAdmin($client);

        $client->request('GET', '/feature/editFeature/999999999');

        self::assertResponseStatusCodeSame(404);
    }

    public function testDeletingNonExistentFeatureReturns404(): void
    {
        $client = static::createClient();
        $this->loginAsAdmin($client);

        // Missing features return 404 before CSRF validation.
        $client->request('POST', '/feature/deleteFeature/999999999');

        self::assertResponseStatusCodeSame(404);
    }

    /** Creates a test administrator and authenticates the client. */
    private function loginAsAdmin(KernelBrowser $client): void
    {
        $entityManager = static::getContainer()->get(EntityManagerInterface::class);
        $suffix = bin2hex(random_bytes(8));

        $admin = (new User())
            ->setUsername('feature_admin_' . $suffix)
            ->setEmail('feature_admin_' . $suffix . '@example.test')
            ->setPassword('test-password-not-used-by-loginUser')
            ->setRoles(['ROLE_ADMIN']);

        $entityManager->persist($admin);
        $entityManager->flush();

        $client->loginUser($admin);
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

    public function testRepositoryIsRegisteredWithDoctrine(): void
    {
        static::createClient();

        $entityManager = static::getContainer()->get(EntityManagerInterface::class);

        $repository = $entityManager->getRepository(WebsiteFeature::class);

        self::assertInstanceOf(
            WebsiteFeatureRepository::class,
            $repository
        );
    }

    public function testFeatureCannotBeCreatedWithBlankFields(): void
    {
        $client = static::createClient();
        $this->loginAsAdmin($client);

        $client->request('GET', '/feature/createFeature');

        self::assertResponseIsSuccessful();

        $client->submitForm('Create Feature', [
            'create_feature_form[name]' => '   ',
            'create_feature_form[description]' => '   ',
            'create_feature_form[status]' => FeaturesStatusEnum::NEW_FEATURE->value,
            'create_feature_form[priority]' => (string) FeaturePriorityEnum::SOON->value,
        ]);

        self::assertResponseStatusCodeSame(200);

        self::assertSelectorTextContains(
            'body',
            'Feature name cannot be empty.'
        );

        self::assertSelectorTextContains(
            'body',
            'Feature description cannot be empty.'
        );

        $repository = static::getContainer()->get(WebsiteFeatureRepository::class);

        self::assertSame([], array_filter(
            $repository->findAll(),
            fn (WebsiteFeature $feature) => trim($feature->getName() ?? '') === ''
        ));
    }

    public function testFeatureCannotBeCreatedWithTooLongName(): void
    {
        $client = static::createClient();
        $this->loginAsAdmin($client);

        $client->request('GET', '/feature/createFeature');

        self::assertResponseIsSuccessful();

        $longName = str_repeat('A', 256);

        $client->submitForm('Create Feature', [
            'create_feature_form[name]' => $longName,
            'create_feature_form[description]' => 'Valid feature description.',
            'create_feature_form[status]' => FeaturesStatusEnum::NEW_FEATURE->value,
            'create_feature_form[priority]' => (string) FeaturePriorityEnum::SOON->value,
        ]);

        self::assertResponseStatusCodeSame(200);

        self::assertSelectorTextContains(
            'body',
            'Feature name cannot exceed 255 characters.'
        );

        $repository = static::getContainer()->get(WebsiteFeatureRepository::class);

        foreach ($repository->findAll() as $feature) {
            self::assertNotSame($longName, $feature->getName());
        }
    }

    public function testFeaturesAreOrderedByPriority(): void
    {
        $client = static::createClient();

        $entityManager = static::getContainer()->get(EntityManagerInterface::class);

        // Insert features out of priority order to verify sorting.
        $this->createFeature(
            $entityManager,
            'Low Priority Feature',
            'Feature with the lowest priority.',
            FeaturesStatusEnum::NEW_FEATURE,
            FeaturePriorityEnum::NICE_TO_HAVE
        );

        $this->createFeature(
            $entityManager,
            'High Priority Feature',
            'Feature with the highest priority.',
            FeaturesStatusEnum::NEW_FEATURE,
            FeaturePriorityEnum::ASAP
        );

        $this->createFeature(
            $entityManager,
            'Medium Priority Feature',
            'Feature with medium priority.',
            FeaturesStatusEnum::NEW_FEATURE,
            FeaturePriorityEnum::SOON
        );

        $crawler = $client->request('GET', '/feature/list');

        self::assertResponseIsSuccessful();

        $toDoSection = $crawler->filter('section.features-section')->eq(0);

        $featureNames = $toDoSection
            ->filter('.feature-row .feature-name')
            ->each(fn ($node) => trim($node->text()));

        self::assertSame([
            'High Priority Feature',
            'Medium Priority Feature',
            'Low Priority Feature',
        ], $featureNames);
    }

    public function testFeatureCanBeCreatedWithDeadline(): void
    {
        $client = static::createClient();
        $this->loginAsAdmin($client);

        $client->request('GET', '/feature/createFeature');

        self::assertResponseIsSuccessful();

        $client->submitForm('Create Feature', [
            'create_feature_form[name]' => 'Feature With Deadline',
            'create_feature_form[description]' => 'Testing deadline persistence.',
            'create_feature_form[status]' => FeaturesStatusEnum::NEW_FEATURE->value,
            'create_feature_form[priority]' => (string) FeaturePriorityEnum::SOON->value,
            'create_feature_form[deadlineAt]' => '2027-06-15',
        ]);

        self::assertResponseRedirects();

        $entityManager = static::getContainer()->get(EntityManagerInterface::class);

        $entityManager->clear();

        $feature = $entityManager
            ->getRepository(WebsiteFeature::class)
            ->findOneBy(['name' => 'Feature With Deadline']);

        self::assertNotNull($feature);

        self::assertInstanceOf(
            DateTimeImmutable::class,
            $feature->getDeadlineAt()
        );

        self::assertSame(
            '2027-06-15',
            $feature->getDeadlineAt()->format('Y-m-d')
        );
    }

    public function testInvalidCsrfTokenPreventsFeatureCreation(): void
    {
        $this->assertFeatureCreationRejectedByCsrf('invalid-token');
    }

    public function testMissingCsrfTokenPreventsFeatureCreation(): void
    {
        $this->assertFeatureCreationRejectedByCsrf(null);
    }

    public function testInvalidCsrfTokenPreventsFeatureEdit(): void
    {
        $this->assertFeatureEditRejectedByCsrf('invalid-token');
    }

    public function testMissingCsrfTokenPreventsFeatureEdit(): void
    {
        $this->assertFeatureEditRejectedByCsrf(null);
    }

    private function assertFeatureCreationRejectedByCsrf(?string $token): void
    {
        $client = static::createClient();
        $this->loginAsAdmin($client);

        $name = 'CSRF Protected Feature ' . bin2hex(random_bytes(6));

        $client->request('GET', '/feature/createFeature');
        self::assertResponseIsSuccessful();

        $data = [
            'name' => $name,
            'description' => 'This should never be saved.',
            'status' => FeaturesStatusEnum::NEW_FEATURE->value,
            'priority' => (string) FeaturePriorityEnum::SOON->value,
        ];

        if ($token !== null) {
            $data['_token'] = $token;
        }

        $client->request('POST', '/feature/createFeature', [
            'create_feature_form' => $data,
        ]);

        self::assertResponseIsSuccessful();
        self::assertSelectorExists('form');

        $em = static::getContainer()->get(EntityManagerInterface::class);
        $em->clear();

        self::assertNull(
            $em->getRepository(WebsiteFeature::class)->findOneBy([
                'name' => $name,
            ])
        );
    }

    private function assertFeatureEditRejectedByCsrf(?string $token): void
    {
        $client = static::createClient();
        $this->loginAsAdmin($client);

        $em = static::getContainer()->get(EntityManagerInterface::class);

        $feature = $this->createFeature(
            $em,
            'Original CSRF Feature ' . bin2hex(random_bytes(6)),
            'Original description.',
            FeaturesStatusEnum::NEW_FEATURE,
            FeaturePriorityEnum::SOON
        );

        $id = $feature->getId();
        $originalName = $feature->getName();
        $url = '/feature/editFeature/' . $id;

        $client->request('GET', $url);
        self::assertResponseIsSuccessful();

        $data = [
            'name' => 'Unauthorized CSRF Edit',
            'description' => 'This should never be saved.',
            'status' => FeaturesStatusEnum::COMPLETED_FEATURE->value,
            'priority' => (string) FeaturePriorityEnum::ASAP->value,
        ];

        if ($token !== null) {
            $data['_token'] = $token;
        }

        $client->request('POST', $url, [
            'create_feature_form' => $data,
        ]);

        self::assertResponseIsSuccessful();
        self::assertSelectorExists('form');

        $em = static::getContainer()->get(EntityManagerInterface::class);
        $em->clear();

        $stored = $em->getRepository(WebsiteFeature::class)->find($id);

        self::assertInstanceOf(WebsiteFeature::class, $stored);
        self::assertSame($originalName, $stored->getName());
        self::assertSame('Original description.', $stored->getDescription());
        self::assertSame(
            FeaturesStatusEnum::NEW_FEATURE,
            $stored->getStatus()
        );
        self::assertSame(
            FeaturePriorityEnum::SOON,
            $stored->getPriority()
        );
    }
}


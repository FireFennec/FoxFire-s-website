<?php

declare(strict_types=1);

namespace App\Tests\Repository;

use App\Repository\WebsiteFeatureRepository;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\WebsiteFeature;
use App\Model\FeaturePriorityEnum;
use App\Model\FeaturesStatusEnum;

class WebsiteFeatureRepositoryTest extends KernelTestCase
{
    private WebsiteFeatureRepository $repository;
    private EntityManagerInterface $entityManager;
    private Connection $connection;

    protected function setUp(): void
    {
        self::bootKernel();

        $container = static::getContainer();

        $this->entityManager = $container->get(EntityManagerInterface::class);
        $this->repository = $container->get(WebsiteFeatureRepository::class);

        $this->connection = $this->entityManager->getConnection();
        $this->connection->beginTransaction();
    }

    protected function tearDown(): void
    {
        if ($this->connection->isTransactionActive()) {
            $this->connection->rollBack();
        }

        $this->entityManager->close();

        parent::tearDown();
    }

    public function testFindRecentlyCompletedReturnsMaximumFifteenFeatures(): void
    {
        for ($i = 1; $i <= 20; $i++) {
            $feature = new WebsiteFeature();

            $feature->setName('Test completed feature ' . $i);
            $feature->setDescription('Repository integration test');
            $feature->setStatus(FeaturesStatusEnum::COMPLETED_FEATURE);
            $feature->setPriority(FeaturePriorityEnum::ANYTIME);

            $this->entityManager->persist($feature);
        }

        $this->entityManager->flush();

        $completedFeatures = $this->repository->findRecentlyCompleted();

        self::assertCount(15, $completedFeatures);
    }
}

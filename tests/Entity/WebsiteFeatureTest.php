<?php

declare(strict_types=1);

namespace App\Tests\Entity;

use App\Entity\WebsiteFeature;
use App\Model\FeaturesStatusEnum;
use PHPUnit\Framework\TestCase;

class WebsiteFeatureTest extends TestCase
{
    public function testStatusStringIsNullWhenStatusIsNotSet(): void
    {
        $feature = new WebsiteFeature();

        self::assertNull($feature->getStatusString());
    }

    public function testStatusStringReturnsEnumValue(): void
    {
        $feature = new WebsiteFeature();
        $feature->setStatus(FeaturesStatusEnum::NEW_FEATURE);

        self::assertSame(
            FeaturesStatusEnum::NEW_FEATURE->value,
            $feature->getStatusString()
        );
    }

    public function testCompletedStatusSetsCompletionDate(): void
    {
        $feature = new WebsiteFeature();

        self::assertNull($feature->getCompletedAt());

        $before = new \DateTimeImmutable();

        $feature->setStatus(FeaturesStatusEnum::COMPLETED_FEATURE);

        $after = new \DateTimeImmutable();

        self::assertNotNull($feature->getCompletedAt());
        self::assertGreaterThanOrEqual(
            $before,
            $feature->getCompletedAt()
        );
        self::assertLessThanOrEqual(
            $after,
            $feature->getCompletedAt()
        );
    }

    public function testCompletedStatusDoesNotOverwriteCompletionDate(): void
    {
        $feature = new WebsiteFeature();

        $feature->setStatus(FeaturesStatusEnum::COMPLETED_FEATURE);

        $originalCompletedAt = $feature->getCompletedAt();

        $feature->setStatus(FeaturesStatusEnum::COMPLETED_FEATURE);

        self::assertSame(
            $originalCompletedAt,
            $feature->getCompletedAt()
        );
    }

    public function testChangingCompletedStatusToNewClearsCompletionDate(): void
    {
        $feature = new WebsiteFeature();

        $feature->setStatus(FeaturesStatusEnum::COMPLETED_FEATURE);

        self::assertNotNull($feature->getCompletedAt());

        $feature->setStatus(FeaturesStatusEnum::NEW_FEATURE);

        self::assertNull($feature->getCompletedAt());
    }

    public function testRecompletingFeatureSetsNewCompletionDate(): void
    {
        $feature = new WebsiteFeature();

        $feature->setStatus(FeaturesStatusEnum::COMPLETED_FEATURE);

        $originalCompletedAt = $feature->getCompletedAt();

        $feature->setStatus(FeaturesStatusEnum::UPDATE_FEATURE);

        self::assertNull($feature->getCompletedAt());

        $feature->setStatus(FeaturesStatusEnum::COMPLETED_FEATURE);

        self::assertNotNull($feature->getCompletedAt());
        self::assertNotSame(
            $originalCompletedAt,
            $feature->getCompletedAt()
        );
        self::assertGreaterThanOrEqual(
            $originalCompletedAt,
            $feature->getCompletedAt()
        );
    }
}

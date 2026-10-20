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
}

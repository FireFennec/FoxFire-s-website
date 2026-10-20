<?php

declare(strict_types=1);

namespace App\Tests\Model;

use App\Model\PersonalSkillLevelEnum;
use PHPUnit\Framework\TestCase;

class PersonalSkillLevelEnumTest extends TestCase
{
    public function testAllLevelsHaveCorrectValues(): void
    {
        $expectedValues = [1, 2, 3, 4, 5, 6];

        $actualValues = array_map(
            static fn (PersonalSkillLevelEnum $level): int => $level->value,
            PersonalSkillLevelEnum::cases()
        );

        self::assertSame($expectedValues, $actualValues);
    }

    public function testAllLevelsHaveCorrectLabels(): void
    {
        $expectedLabels = [
            'Amateur',
            'Beginner',
            'Advanced',
            'Experienced',
            'Professional',
            'Master',
        ];

        $actualLabels = array_map(
            static fn (PersonalSkillLevelEnum $level): string => $level->label(),
            PersonalSkillLevelEnum::cases()
        );

        self::assertSame($expectedLabels, $actualLabels);
    }

    public function testAllLevelsHaveCorrectDisplayLabels(): void
    {
        $expectedLabels = [
            'Amateur (1/6)',
            'Beginner (2/6)',
            'Advanced (3/6)',
            'Experienced (4/6)',
            'Professional (5/6)',
            'Master (6/6)',
        ];

        $actualLabels = array_map(
            static fn (PersonalSkillLevelEnum $level): string => $level->displayLabel(),
            PersonalSkillLevelEnum::cases()
        );

        self::assertSame($expectedLabels, $actualLabels);
    }

    public function testEnumCanBeCreatedFromInteger(): void
    {
        self::assertSame(
            PersonalSkillLevelEnum::EXPERIENCED,
            PersonalSkillLevelEnum::from(4)
        );
    }
}

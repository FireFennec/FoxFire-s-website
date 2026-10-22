<?php

declare(strict_types=1);

namespace App\Model;

/**
 * Defines personal skill proficiency levels on a six-point scale.
 */
enum PersonalSkillLevelEnum: int
{
    case AMATEUR = 1;
    case BEGINNER = 2;
    case ADVANCED = 3;
    case EXPERIENCED = 4;
    case PROFESSIONAL = 5;
    case MASTER = 6;

    public function label(): string
    {
        return match ($this) {
            self::AMATEUR => 'Amateur',
            self::BEGINNER => 'Beginner',
            self::ADVANCED => 'Advanced',
            self::EXPERIENCED => 'Experienced',
            self::PROFESSIONAL => 'Professional',
            self::MASTER => 'Master',
        };
    }

    /**
     * Returns the proficiency label with its rating on a six-point scale.
     */
    public function displayLabel(): string
    {
        return sprintf('%s (%d/6)', $this->label(), $this->value);
    }
}

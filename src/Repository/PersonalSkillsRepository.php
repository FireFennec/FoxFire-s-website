<?php

namespace App\Repository;

use App\Model\PersonalSkillLevelEnum;
use App\Model\PersonalSkills;

class PersonalSkillsRepository
{
    public function findAll(): array
    {
        return [
            new PersonalSkills(
                0,
                'PHP (Hypertext Preprocessor)',
                'A back-end programming language used to develop web applications.',
                PersonalSkillLevelEnum::EXPERIENCED
            ),
            new PersonalSkills(
                1,
                'Symfony (PHP framework)',
                'A PHP framework (like a comprehensive library of functions and tools).
                The best way to write professional, maintainable PHP code.',
                PersonalSkillLevelEnum::ADVANCED
            ),
            new PersonalSkills(
                2,
                'HTML (Hypertext Markup Language)',
                'A front-end programming language designed for building the user-facing visual layer of websites,
                including layouts, styling, animations, and responsive designs.',
                PersonalSkillLevelEnum::ADVANCED
            ),
            new PersonalSkills(
                3,
                'CSS (Cascading Style Sheets)',
                'A front-end stylesheet language specifically designed for styling and designing
                the visual presentation of websites, controlling layouts, colors, fonts, spacing,
                animations, and responsive behavior across different devices.',
                PersonalSkillLevelEnum::BEGGER
            ),
            new PersonalSkills(
                4,
                'English',
                'Important in IT technology.',
                PersonalSkillLevelEnum::PROFESSIONAL
            ),
        ];
    }
}

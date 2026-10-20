<?php

declare(strict_types=1);

namespace App\Tests\Entity;

use App\Entity\PersonalSkill;
use App\Model\PersonalSkillLevelEnum;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class PersonalSkillValidationTest extends KernelTestCase
{
    private ValidatorInterface $validator;

    protected function setUp(): void
    {
        parent::setUp();

        self::bootKernel();

        $this->validator = static::getContainer()->get(
            ValidatorInterface::class
        );
    }

    public function testValidSkillPassesValidation(): void
    {
        $skill = $this->createValidSkill();

        $violations = $this->validator->validate($skill);

        self::assertCount(0, $violations, (string) $violations);
    }

    public function testEmptySkillIsRejected(): void
    {
        $skill = new PersonalSkill();

        $this->assertInvalidFields(
            $skill,
            ['skillName', 'skillDescription', 'levelOfSkill']
        );
    }

    public function testBlankNameIsRejected(): void
    {
        $skill = $this->createValidSkill();
        $skill->setSkillName('   ');

        $this->assertInvalidFields($skill, ['skillName']);
    }

    public function testBlankDescriptionIsRejected(): void
    {
        $skill = $this->createValidSkill();
        $skill->setSkillDescription('   ');

        $this->assertInvalidFields($skill, ['skillDescription']);
    }

    public function testNameLongerThan255CharactersIsRejected(): void
    {
        $skill = $this->createValidSkill();
        $skill->setSkillName(str_repeat('A', 256));

        $this->assertInvalidFields($skill, ['skillName']);
    }

    public function testDescriptionLongerThan2000CharactersIsRejected(): void
    {
        $skill = $this->createValidSkill();
        $skill->setSkillDescription(str_repeat('A', 2001));

        $this->assertInvalidFields($skill, ['skillDescription']);
    }

    public function testMaximumAllowedLengthsAreAccepted(): void
    {
        $skill = $this->createValidSkill();
        $skill->setSkillName(str_repeat('A', 255));
        $skill->setSkillDescription(str_repeat('B', 2000));

        $violations = $this->validator->validate($skill);

        self::assertCount(0, $violations, (string) $violations);
    }

    public function testTextInputsAreTrimmed(): void
    {
        $skill = $this->createValidSkill();
        $skill->setSkillName('  Symfony  ');
        $skill->setSkillDescription('  PHP framework  ');

        self::assertSame('Symfony', $skill->getSkillName());
        self::assertSame(
            'PHP framework',
            $skill->getSkillDescription()
        );
    }

    public function testSkillLevelIsStoredCorrectly(): void
    {
        $skill = $this->createValidSkill();

        self::assertSame(
            PersonalSkillLevelEnum::ADVANCED,
            $skill->getLevelOfSkill()
        );
    }

    private function createValidSkill(): PersonalSkill
    {
        return (new PersonalSkill())
            ->setSkillName('Symfony')
            ->setSkillDescription('PHP framework experience.')
            ->setLevelOfSkill(PersonalSkillLevelEnum::ADVANCED);
    }

    /**
     * @param list<string> $expectedFields
     */
    private function assertInvalidFields(
        PersonalSkill $skill,
        array $expectedFields
    ): void {
        $violations = $this->validator->validate($skill);
        $invalidFields = [];

        foreach ($violations as $violation) {
            $invalidFields[] = $violation->getPropertyPath();
        }

        foreach ($expectedFields as $field) {
            self::assertContains($field, $invalidFields);
        }
    }
}

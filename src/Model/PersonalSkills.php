<?php

namespace App\Model;

class PersonalSkills
{
    public function __construct(
        private int $id,
        private string $skillName,
        private string $description,
        private PersonalSkillLevelEnum $levelOfSkill,
    ) {
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $id): void
    {
        $this->id = $id;
    }

    public function getSkillName(): string
    {
        return $this->skillName;
    }

    public function setSkillName(string $skillName): void
    {
        $this->skillName = $skillName;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function setDescription(string $description): void
    {
        $this->description = $description;
    }

    public function getLevelOfSkill(): PersonalSkillLevelEnum
    {
        return $this->levelOfSkill;
    }

    public function setLevelOfSkill(PersonalSkillLevelEnum $levelOfSkill): void
    {
        $this->levelOfSkill = $levelOfSkill;
    }

    public function getLevelOfSkillString(): string
    {
        return $this->levelOfSkill->value;
    }
}

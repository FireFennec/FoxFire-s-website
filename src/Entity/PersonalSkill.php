<?php

namespace App\Entity;

use App\Model\PersonalSkillLevelEnum;
use App\Repository\PersonalSkillRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PersonalSkillRepository::class)]
class PersonalSkill
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $skillName = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $skillDescription = null;

    #[ORM\Column(enumType: PersonalSkillLevelEnum::class)]
    private ?PersonalSkillLevelEnum $levelOfSkill = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getSkillName(): ?string
    {
        return $this->skillName;
    }

    public function setSkillName(string $skillName): static
    {
        $this->skillName = $skillName;

        return $this;
    }

    public function getSkillDescription(): ?string
    {
        return $this->skillDescription;
    }

    public function setSkillDescription(string $skillDescription): static
    {
        $this->skillDescription = $skillDescription;

        return $this;
    }

    public function getLevelOfSkill(): ?PersonalSkillLevelEnum
    {
        return $this->levelOfSkill;
    }

    public function setLevelOfSkill(PersonalSkillLevelEnum $levelOfSkill): static
    {
        $this->levelOfSkill = $levelOfSkill;

        return $this;
    }
}

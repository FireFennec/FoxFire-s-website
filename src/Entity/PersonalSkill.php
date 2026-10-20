<?php

declare(strict_types=1);

namespace App\Entity;

use App\Model\PersonalSkillLevelEnum;
use App\Repository\PersonalSkillRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: PersonalSkillRepository::class)]
class PersonalSkill
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank]
    #[Assert\Length(max: 255)]
    private ?string $skillName = null;

    #[ORM\Column(type: Types::TEXT)]
    #[Assert\NotBlank]
    #[Assert\Length(max: 2000)]
    private ?string $skillDescription = null;

    #[ORM\Column(enumType: PersonalSkillLevelEnum::class)]
    #[Assert\NotNull]
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
        $this->skillName = trim($skillName);

        return $this;
    }

    public function getSkillDescription(): ?string
    {
        return $this->skillDescription;
    }

    public function setSkillDescription(string $skillDescription): static
    {
        $this->skillDescription = trim($skillDescription);

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

    public function getLevelOfSkillString(): string
    {
        return $this->levelOfSkill?->displayLabel() ?? '';
    }
}

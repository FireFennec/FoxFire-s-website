<?php

declare(strict_types=1);

namespace App\Entity;

use App\Model\EncounterSideEnum;
use App\Repository\EncounterCombatantRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: EncounterCombatantRepository::class)]
class EncounterCombatant
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 100)]
    #[Assert\NotBlank]
    #[Assert\Length(max: 100)]
    private ?string $name = null;

    #[ORM\Column(type: 'string', length: 10, enumType: EncounterSideEnum::class)]
    #[Assert\NotNull]
    private ?EncounterSideEnum $side = null;

    #[ORM\Column]
    #[Assert\Positive]
    private ?int $maxHp = null;

    #[ORM\Column]
    #[Assert\PositiveOrZero]
    #[Assert\LessThanOrEqual(propertyPath: 'maxHp')]
    private ?int $currentHp = null;

    #[ORM\Column]
    #[Assert\NotNull]
    private ?int $initiative = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getSide(): ?EncounterSideEnum
    {
        return $this->side;
    }

    public function setSide(EncounterSideEnum $side): static
    {
        $this->side = $side;

        return $this;
    }

    public function getMaxHp(): ?int
    {
        return $this->maxHp;
    }

    public function setMaxHp(int $maxHp): static
    {
        $this->maxHp = $maxHp;

        return $this;
    }

    public function getCurrentHp(): ?int
    {
        return $this->currentHp;
    }

    public function setCurrentHp(int $currentHp): static
    {
        $this->currentHp = $currentHp;

        return $this;
    }

    public function getInitiative(): ?int
    {
        return $this->initiative;
    }

    public function setInitiative(int $initiative): static
    {
        $this->initiative = $initiative;

        return $this;
    }
}

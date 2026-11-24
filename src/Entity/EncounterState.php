<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\EncounterStateRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: EncounterStateRepository::class)]
class EncounterState
{
    #[ORM\Id]
    #[ORM\Column]
    private int $id = 1;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: true, onDelete: 'SET NULL')]
    private ?EncounterCombatant $activeCombatant = null;

    #[ORM\Column]
    private int $roundNumber = 1;

    public function getId(): int
    {
        return $this->id;
    }

    public function getActiveCombatant(): ?EncounterCombatant
    {
        return $this->activeCombatant;
    }

    public function setActiveCombatant(
        ?EncounterCombatant $activeCombatant
    ): static {
        $this->activeCombatant = $activeCombatant;

        return $this;
    }

    public function getRoundNumber(): int
    {
        return $this->roundNumber;
    }

    public function setRoundNumber(int $roundNumber): static
    {
        $this->roundNumber = $roundNumber;

        return $this;
    }
}

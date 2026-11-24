<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\EncounterCombatant;
use App\Entity\EncounterState;
use App\Repository\EncounterCombatantRepository;
use App\Repository\EncounterStateRepository;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Manages encounter initiative order and turn progression.
 */
final class EncounterInitiativeService
{
    public function __construct(
        private readonly EncounterCombatantRepository $combatantRepository,
        private readonly EncounterStateRepository $stateRepository,
        private readonly EntityManagerInterface $entityManager
    ) {
    }

    /**
     * Returns the initialized encounter state.
     */
    public function getState(): EncounterState
    {
        return $this->stateRepository->find(1)
            ?? throw new \LogicException('Encounter state has not been initialized.');
    }

    /**
     * Returns all combatants ordered by initiative.
     *
     * @return EncounterCombatant[]
     */
    public function getInitiativeOrder(): array
    {
        return $this->combatantRepository->findBy(
            [],
            ['initiative' => 'DESC', 'id' => 'ASC']
        );
    }

    /**
     * Advances the encounter to the next combatant.
     */
    public function nextTurn(): EncounterState
    {
        $state = $this->getState();
        $combatants = $this->getInitiativeOrder();

        if ($combatants === []) {
            $state->setActiveCombatant(null);
            $state->setRoundNumber(1);

            $this->entityManager->flush();

            return $state;
        }

        $activeCombatant = $state->getActiveCombatant();

        if ($activeCombatant === null) {
            $state->setActiveCombatant($combatants[0]);

            $this->entityManager->flush();

            return $state;
        }

        foreach ($combatants as $index => $combatant) {
            if ($combatant->getId() !== $activeCombatant->getId()) {
                continue;
            }

            $nextIndex = $index + 1;

            if ($nextIndex >= count($combatants)) {
                $nextIndex = 0;

                $state->setRoundNumber(
                    $state->getRoundNumber() + 1
                );
            }

            $state->setActiveCombatant($combatants[$nextIndex]);

            $this->entityManager->flush();

            return $state;
        }

        // Recover when the active combatant is no longer available.
        $state->setActiveCombatant($combatants[0]);

        $this->entityManager->flush();

        return $state;
    }

    /**
     * Resets the encounter to its initial round and turn state.
     */
    public function reset(): EncounterState
    {
        $state = $this->getState();

        $state->setActiveCombatant(null);
        $state->setRoundNumber(1);

        $this->entityManager->flush();

        return $state;
    }
}

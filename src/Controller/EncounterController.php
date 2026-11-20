<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\EncounterCombatant;
use App\Form\DnD\EncounterCombatantType;
use App\Model\EncounterSideEnum;
use App\Repository\EncounterCombatantRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

/**
 * Handles the D&D encounter tracker page.
 */
#[Route('/dnd/encounter')]
final class EncounterController extends AbstractController
{
    /**
     * Displays the encounter tracker or an access information page.
     */
    #[Route('', name: 'app_dnd_encounter_index', methods: ['GET'])]
    public function index(
        EncounterCombatantRepository $combatantRepository
    ): Response {
        if (!$this->isGranted('ROLE_DND_PLAYER')) {
            return $this->render('dnd/encounter/access_denied.html.twig');
        }

        $allies = $combatantRepository->findBy(
            ['side' => EncounterSideEnum::ALLY],
            ['initiative' => 'DESC']
        );

        $enemies = $combatantRepository->findBy(
            ['side' => EncounterSideEnum::ENEMY],
            ['initiative' => 'DESC']
        );

        return $this->render('dnd/encounter/index.html.twig', [
            'allies' => $allies,
            'enemies' => $enemies,
        ]);
    }

    /**
     * Creates a new encounter combatant.
     */
    #[Route('/new', name: 'app_dnd_encounter_new', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_DND_DM')]
    public function new(
        Request $request,
        EntityManagerInterface $entityManager
    ): Response {
        $combatant = new EncounterCombatant();
        $combatant->setSide(EncounterSideEnum::ENEMY);

        $form = $this->createForm(EncounterCombatantType::class, $combatant);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            // New combatants always start with full HP.
            $maxHp = $combatant->getMaxHp();

            if ($maxHp !== null && $maxHp > 0) {
                $combatant->setCurrentHp($maxHp);
            }

            if ($form->isValid()) {
                $entityManager->persist($combatant);
                $entityManager->flush();

                $this->addFlash('success', 'Combatant successfully added.');

                return $this->redirectToRoute('app_dnd_encounter_index');
            }
        }

        return $this->render('dnd/encounter/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * Edits an existing encounter combatant.
     */
    #[Route(
        '/{id}/edit',
        name: 'app_dnd_encounter_edit',
        methods: ['GET', 'POST'],
        requirements: ['id' => '\d+']
    )]
    #[IsGranted('ROLE_DND_DM')]
    public function edit(
        EncounterCombatant $combatant,
        Request $request,
        EntityManagerInterface $entityManager
    ): Response {
        $originalMaxHp = $combatant->getMaxHp();

        $form = $this->createForm(EncounterCombatantType::class, $combatant);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Prevent current HP from exceeding the new maximum HP.
            if ($combatant->getMaxHp() !== $originalMaxHp) {
                $combatant->setCurrentHp(
                    min($combatant->getCurrentHp(), $combatant->getMaxHp())
                );
            }

            $entityManager->flush();

            $this->addFlash('success', 'Combatant successfully updated.');

            return $this->redirectToRoute('app_dnd_encounter_index');
        }

        return $this->render('dnd/encounter/edit.html.twig', [
            'form' => $form->createView(),
            'combatant' => $combatant,
        ]);
    }

    /**
     * Returns the current state of all encounter combatants.
     */
    #[Route(
        '/hp-status',
        name: 'app_dnd_encounter_hp_status',
        methods: ['GET']
    )]
    #[IsGranted('ROLE_DND_PLAYER')]
    public function hpStatus(
        EncounterCombatantRepository $combatantRepository
    ): JsonResponse {
        $combatants = $combatantRepository->findBy(
            [],
            ['initiative' => 'DESC', 'id' => 'ASC']
        );

        $combatantStatus = [];

        foreach ($combatants as $combatant) {
            $combatantStatus[] = [
                'id' => $combatant->getId(),
                'name' => $combatant->getName(),
                'side' => $combatant->getSide()->value,
                'initiative' => $combatant->getInitiative(),
                'currentHp' => $combatant->getCurrentHp(),
                'maxHp' => $combatant->getMaxHp(),
            ];
        }

        return $this->json([
            'combatants' => $combatantStatus,
        ], headers: [
            'Cache-Control' => 'no-store',
        ]);
    }

    /**
     * Returns rendered combatant lists for automatic synchronization.
     */
    #[Route(
        '/combatants-html',
        name: 'app_dnd_encounter_combatants_html',
        methods: ['GET']
    )]
    #[IsGranted('ROLE_DND_PLAYER')]
    public function combatantsHtml(
        EncounterCombatantRepository $combatantRepository
    ): JsonResponse {
        $allies = $combatantRepository->findBy(
            ['side' => EncounterSideEnum::ALLY],
            ['initiative' => 'DESC']
        );

        $enemies = $combatantRepository->findBy(
            ['side' => EncounterSideEnum::ENEMY],
            ['initiative' => 'DESC']
        );

        return $this->json([
            'allies' => $this->renderView(
                'dnd/encounter/_combatant_list.html.twig',
                [
                    'combatants' => $allies,
                    'emptyMessage' => 'No allies added yet.',
                ]
            ),
            'enemies' => $this->renderView(
                'dnd/encounter/_combatant_list.html.twig',
                [
                    'combatants' => $enemies,
                    'emptyMessage' => 'No enemies added yet.',
                ]
            ),
        ], headers: [
            'Cache-Control' => 'no-store',
        ]);
    }

    /**
     * Applies damage or healing to a combatant.
     */
    #[Route(
        '/{id}/hp',
        name: 'app_dnd_encounter_hp',
        methods: ['POST'],
        requirements: ['id' => '\d+']
    )]
    #[IsGranted('ROLE_DND_PLAYER')]
    public function updateHp(
        EncounterCombatant $combatant,
        Request $request,
        EntityManagerInterface $entityManager
    ): JsonResponse {
        // Players can modify allies, while DMs can modify both sides.
        if (
            $combatant->getSide() !== EncounterSideEnum::ALLY
            && !$this->isGranted('ROLE_DND_DM')
        ) {
            return $this->json(
                ['error' => 'You cannot modify enemy HP.'],
                Response::HTTP_FORBIDDEN
            );
        }

        // Protect the endpoint against CSRF attacks.
        if (!$this->isCsrfTokenValid(
            'encounter_hp_' . $combatant->getId(),
            (string) $request->request->get('_token', '')
        )) {
            return $this->json(
                ['error' => 'Invalid CSRF token.'],
                Response::HTTP_FORBIDDEN
            );
        }

        $amount = filter_var(
            $request->request->get('amount'),
            FILTER_VALIDATE_INT
        );

        $action = $request->request->get('action');

        if ($amount === false || $amount <= 0) {
            return $this->json(
                ['error' => 'Amount must be a positive integer.'],
                Response::HTTP_BAD_REQUEST
            );
        }

        if (!in_array($action, ['damage', 'heal'], true)) {
            return $this->json(
                ['error' => 'Invalid HP action.'],
                Response::HTTP_BAD_REQUEST
            );
        }

        $currentHp = $combatant->getCurrentHp();
        $maxHp = $combatant->getMaxHp();

        if ($action === 'damage') {
            $newHp = max(0, $currentHp - $amount);
        } else {
            $newHp = min($maxHp, $currentHp + $amount);
        }

        $combatant->setCurrentHp($newHp);
        $entityManager->flush();

        return $this->json([
            'success' => true,
            'currentHp' => $combatant->getCurrentHp(),
            'maxHp' => $combatant->getMaxHp(),
        ]);
    }

    /**
     * Deletes an existing encounter combatant.
     */
    #[Route(
        '/{id}/delete',
        name: 'app_dnd_encounter_delete',
        methods: ['POST'],
        requirements: ['id' => '\d+']
    )]
    #[IsGranted('ROLE_DND_DM')]
    public function delete(
        EncounterCombatant $combatant,
        Request $request,
        EntityManagerInterface $entityManager
    ): Response {
        if (!$this->isCsrfTokenValid(
            'encounter_delete_' . $combatant->getId(),
            (string) $request->request->get('_token', '')
        )) {
            throw $this->createAccessDeniedException('Invalid CSRF token.');
        }

        $entityManager->remove($combatant);
        $entityManager->flush();

        $this->addFlash('success', 'Combatant successfully deleted.');

        return $this->redirectToRoute('app_dnd_encounter_index');
    }
}

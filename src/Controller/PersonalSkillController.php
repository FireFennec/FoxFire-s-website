<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\PersonalSkill;
use App\Form\PersonalSkills\PersonalSkillType;
use App\Repository\PersonalSkillRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

/**
 * Handles listing, creating, and editing personal skills.
 */
#[Route('/skills')]
class PersonalSkillController extends AbstractController
{
    /**
     * Displays personal skills ordered by proficiency level.
     */
    #[Route('', name: 'app_personal_skill_index', methods: ['GET'])]
    public function index(PersonalSkillRepository $repository): Response
    {
        $skills = $repository->findAllOrderedByLevel();

        return $this->render('personalSkill/listOfMySkills.html.twig', [
            'skills' => $skills,
        ]);
    }

    /**
     * Displays and processes the form for creating a personal skill.
     */
    #[Route('/create', name: 'app_personal_skill_create', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function create(
        Request $request,
        EntityManagerInterface $entityManager
    ): Response {
        $skill = new PersonalSkill();

        $form = $this->createForm(PersonalSkillType::class, $skill, [
            'submit_label' => 'Create Skill',
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($skill);
            $entityManager->flush();

            return $this->redirectToRoute('app_personal_skill_index');
        }

        return $this->render('personalSkill/createSkill.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * Displays and processes the form for editing an existing personal skill.
     */
    #[Route('/{id<\d+>}/edit', name: 'app_personal_skill_edit', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function edit(
        int $id,
        Request $request,
        EntityManagerInterface $entityManager,
        PersonalSkillRepository $repository
    ): Response {
        $skill = $repository->find($id);

        if ($skill === null) {
            throw $this->createNotFoundException('Personal skill not found.');
        }

        $form = $this->createForm(PersonalSkillType::class, $skill, [
            'submit_label' => 'Save Changes',
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // The existing entity is already managed by Doctrine.
            $entityManager->flush();

            return $this->redirectToRoute('app_personal_skill_index');
        }

        return $this->render('personalSkill/editSkill.html.twig', [
            'form' => $form->createView(),
            'skill' => $skill,
        ]);
    }
}

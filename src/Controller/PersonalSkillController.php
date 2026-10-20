<?php

declare(strict_types = 1);

namespace App\Controller;

use App\Repository\PersonalSkillRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/skills')]
class PersonalSkillController extends AbstractController
{
    #[Route('', name: 'app_personal_skill_index', methods: ['GET'])]
    public function index(PersonalSkillRepository $repository): Response
    {
        $skills = $repository->findAllOrderedByLevel();

        return $this->render('personalSkill/listOfMySkills.html.twig', [
            'skills' => $skills,
        ]);
    }
}

<?php

namespace App\Controller;

use App\Repository\PersonalSkillRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/personal-skill')]
class PersonalSkillController extends AbstractController
{
    #[Route('/skillList', name: 'app_personalSkill_listOfMySkills', methods: ['GET'])]
    public function listOfMySkills(PersonalSkillRepository $repository): Response
    {
        $skills = $repository->findAll();

        return $this->render('personalSkill/listOfMySkills.html.twig', [
            'skills' => $skills
        ]);
    }
}

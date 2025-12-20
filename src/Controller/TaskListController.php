<?php

namespace App\Controller;

use App\Repository\WebsiteFeaturesRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/task')]
class TaskListController extends AbstractController
{
    #[Route('/{id<\d+>}', name: 'app_taskList_taskInfoShow')]
    public function taskInfoShow($id, WebsiteFeaturesRepository $repository): Response
    {
        $task = $repository->findOne($id);

        if (!$task) {
            throw $this->createNotFoundException('Task not found');
        }

        return $this->render('task/taskInfoShow.html.twig', [
            'task' => $task
        ]);
    }
}

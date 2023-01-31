<?php

namespace App\Controller;

use App\Entity\Task;
use App\Service\FormService;
use App\Service\TaskService;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class TaskController extends AbstractController
{
    /**
     * @var ManagerRegistry
     */
    private $manager;

    /**
     * @var TaskService
     */
    private $taskService;

    /**
     * @var FormService
     */
    private $formService;

    public function __construct(ManagerRegistry $manager, TaskService $taskService, FormService $formService)
    {
        $this->manager = $manager;
        $this->taskService = $taskService;
        $this->formService = $formService;
    }

    /**
     * @Route("/tasks", name="task_list")
     */
    public function listAction(): Response
    {
        return $this->render('task/list.html.twig', [
            'tasks' => $this->manager->getRepository(Task::class)->findAll()
        ]);
    }

    /**
     * @Route("/tasks/create", name="task_create")
     */
    public function createAction(Request $request): Response
    {
        $task = new Task();

        $taskForm = $this->formService->getTaskForm($request, $task);

        if ($this->taskService->crudTaskManagement($taskForm, $task)) {
            $this->addFlash('success', 'La tâche a été bien été ajoutée.');

            return $this->redirectToRoute('task_list');
        }

        return $this->render('task/create.html.twig', [
            'form' => $taskForm->createView()
        ]);
    }

    /**
     * @Route("/tasks/{id}/edit", name="task_edit")
     */
    public function editAction(Task $task, Request $request): Response
    {
        /**
         * @var User
         */
        $user = $this->getUser();

        if ($user->getId() === $task->getUser()->getId()) {
            $route_name = $request->get('_route');

            $taskForm = $this->formService->getTaskForm($request, $task);
    
            if ($this->taskService->crudTaskManagement($taskForm, $task, $route_name)) {
                $this->addFlash('success', 'La tâche a bien été modifiée.');
    
                return $this->redirectToRoute('task_list');
            }
    
            return $this->render('task/edit.html.twig', [
                'form' => $taskForm->createView(),
                'task' => $task,
            ]);
        }

        $this->addFlash('error', sprintf('Vous ne pouvez pas modifier cette tâche, elle ne vous appartient pas'));

        return $this->redirectToRoute('task_list');
    }

    /**
     * @Route("/tasks/{id}/toggle", name="task_toggle")
     */
    public function toggleTaskAction(Task $task): Response
    {
        /**
         * @var User
         */
        $user = $this->getUser();

        if ($user->getId() === $task->getUser()->getId()) {
            $this->taskService->toggleTask($task);

            $is_done = $task->isDone() ? 'faite' : 'non terminée';

            $this->addFlash('success', sprintf('La tâche %s a bien été marquée comme %s.', $task->getTitle(), $is_done));

            return $this->redirectToRoute('task_list');
        }

        $this->addFlash('error', sprintf('Vous ne pouvez pas modifier cette tâche, elle ne vous appartient pas'));

        return $this->redirectToRoute('task_list');
    }

    /**
     * @Route("/tasks/{id}/delete", name="task_delete")
     */
    public function deleteTaskAction(Task $task): Response
    {
        /**
         * @var User
         */
        $user = $this->getUser();

        if ($user->getRoles()['ROLE'] === 'ROLE_ADMIN' && $task->getUser()->getId() === 0 || $user->getId() === $task->getUser()->getId()) {
            $this->taskService->deleteTask($task);

            $this->addFlash('success', 'La tâche a bien été supprimée.');

            return $this->redirectToRoute('task_list');
        }

        $this->addFlash('error', sprintf('Vous ne pouvez pas supprimer cette tâche, elle ne vous appartient pas'));

        return $this->redirectToRoute('task_list');
    }
}

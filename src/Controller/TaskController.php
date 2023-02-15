<?php

namespace App\Controller;

use App\Entity\Task;
use App\Form\TaskType;
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

    public function __construct(ManagerRegistry $manager, TaskService $taskService)
    {
        $this->manager = $manager;
        $this->taskService = $taskService;
    }

    /**
     * @Route("/tasks/list/{is_done}", name="task_list")
     */
    public function listAction(string $is_done): Response
    {
        $repository = $this->manager->getRepository(Task::class);
        $task = $repository->findTaskList($is_done);

        $type_task = $this->taskService->getTypeTask($is_done);

        return $this->render('task/list.html.twig', [
            'tasks' => $task,
            'type_task' => $type_task
        ]);
    }

    /**
     * @Route("/tasks/create", name="task_create")
     */
    public function createAction(Request $request): Response
    {
        $task = new Task();

        $taskForm = $this->createForm(TaskType::class, $task);
        $taskForm->handleRequest($request);
        if ($this->taskService->crudTaskManagement($taskForm, $task, $this->getUser())) {
            $this->addFlash('success', 'La tâche a été bien été ajoutée.');

            return $this->redirectToRoute('task_list', [
                'is_done' => 'progress'
            ]);
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
        $this->denyAccessUnlessGranted('TASK_EDIT', $task);

        $route_name = $request->get('_route');

        $taskForm = $this->createForm(TaskType::class, $task);
        $taskForm->handleRequest($request);
        if ($this->taskService->crudTaskManagement($taskForm, $task, null, $route_name)) {
            $this->addFlash('success', 'La tâche a bien été modifiée.');

            $is_done = $task->isDone() ? 'ended' : 'progress';

            return $this->redirectToRoute('task_list', [
                'is_done' => $is_done
            ]);
        }

        return $this->render('task/edit.html.twig', [
            'form' => $taskForm->createView(),
            'task' => $task,
        ]);
    }

    /**
     * @Route("/tasks/{id}/toggle", name="task_toggle")
     */
    public function toggleTaskAction(Task $task): Response
    {
        $this->denyAccessUnlessGranted('TASK_TOGGLE', $task);

        $this->taskService->toggleTask($task);

        $is_done = $task->isDone() ? 'faite' : 'non terminée';
        $parameter_is_done = $task->isDone() ? 'ended' : 'progress';

        $this->addFlash(
            'success',
            sprintf('La tâche %s a bien été marquée comme %s.', $task->getTitle(), $is_done)
        );

        return $this->redirectToRoute('task_list', [
            'is_done' => $parameter_is_done
        ]);
    }

    /**
     * @Route("/tasks/{id}/delete", name="task_delete")
     */
    public function deleteTaskAction(Task $task): Response
    {
        $this->denyAccessUnlessGranted('TASK_DELETE', $task);

        $is_done = $task->isDone() ? 'ended' : 'progress';

        $this->taskService->deleteTask($task);

        $this->addFlash('success', 'La tâche a bien été supprimée.');

        return $this->redirectToRoute('task_list', [
            'is_done' => $is_done
        ]);
    }
}

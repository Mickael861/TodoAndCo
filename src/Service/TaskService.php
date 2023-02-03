<?php

namespace App\Service;

use App\Entity\Task;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class TaskService extends AbstractController
{
    /**
     * @var ManagerRegistry
     */
    private $managerRegistry;

    public function __construct(ManagerRegistry $managerRegistry)
    {
        $this->managerRegistry = $managerRegistry;
    }

    /**
     * CRUD task management
     *
     * @param  FormInterface $form task form
     * @param  Task $task Entity Task
     * @param  string $route_name Name of the route
     * @return bool True if the backup is successful, false otherwise
     */
    public function crudTaskManagement(FormInterface $form, Task $task, string $route_name = 'task_create'): bool
    {
        $success = false;

        if ($form->isSubmitted() && $form->isValid()) {
            switch ($route_name) {
                case 'task_edit':
                    $this->updateTask();

                    break;
                default:
                    $this->createTask($task);

                    break;
            }

            $success = true;
        }

        return $success;
    }

    /**
     * Task delete management
     *
     * @param  Task $task Entity Task
     *
     * @return void
     */
    public function deleteTask(Task $task): void
    {
        $entityManager = $this->managerRegistry->getManager();
        $entityManager->remove($task);
        $entityManager->flush();
    }

    /**
     * Task toggle management
     *
     * @param  Task $task Entity Task
     *
     * @return void
     */
    public function toggleTask(Task $task): void
    {
        $task->toggle(!$task->isDone());
        $this->managerRegistry->getManager()->flush();
    }

    /**
     * Managing the modification of a task
     *
     * @return void
     */
    private function updateTask(): void
    {
        $this->managerRegistry->getManager()->flush();
    }

    /**
     * Task backup management
     *
     * @param  Task $task Entity Task
     *
     * @return void
     */
    private function createTask(Task $task): void
    {
        $entityManager = $this->managerRegistry->getManager();

        $task->setUser($this->getUser());

        $entityManager->persist($task);
        $entityManager->flush();
    }

    /**
     * Retrieve completed or in-progress tasks
     *
     * @param  Request $request The request
     * @return array Tasks
     */
    public function getTaskToggle(Request $request): array
    {
        $task = [];

        if (empty($request->get('completed')) || $request->get('completed') > 1) {
            $task = $this->managerRegistry->getRepository(Task::class)->findAll();
        }

        if (empty($task)) {
            $task_completed = true;
            if ((int) $request->get('completed') === 0) {
                $task_completed = false;
            }

            $task = $this->managerRegistry->getRepository(Task::class)->findBy([
                'isDone' => $task_completed
            ]);
        }

        return $task;
    }
}

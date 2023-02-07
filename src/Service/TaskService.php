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
     * get type of task
     *
     * @param  string $is_done Type of task
     * @return string Type of task
     */
    public function getTypeTask(string $is_done): string
    {
        switch ($is_done) {
            case 'ended':
                $type_task = "terminées";

                break;
            case 'progress':
                $type_task = "en cours";

                break;
            default:
                $type_task = '';

                break;
        }

        return $type_task;
    }
}

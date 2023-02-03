<?php

namespace App\Service;

use App\Entity\Task;
use App\Entity\User;
use App\Form\TaskType;
use App\Form\UserType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class FormService extends AbstractController
{
    /**
     * Retrieve stains form task
     *
     * @param  Request $request request
     * @param  Task $task Entity Task
     * @param  array[] $options the options to pass to the form
     */
    public function getTaskForm(Request $request, Task $task, array $options = []): FormInterface
    {
        $form = $this->createForm(TaskType::class, $task, $options);
        $form->handleRequest($request);

        return $form;
    }

    /**
     * Retrieve stains form user
     *
     * @param  Request $request request
     * @param  User $user Entity User
     */
    public function getUserForm(Request $request, User $user): FormInterface
    {
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        return $form;
    }
}

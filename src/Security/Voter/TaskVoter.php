<?php

namespace App\Security\Voter;

use App\Entity\Task;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

/**
 * Managing access for task controller actions
 */
class TaskVoter extends Voter
{
    /**
     * Attribute for edit action
     */
    public const EDIT = 'TASK_EDIT';

    /**
     * Attribute for toggle action
     */
    public const TOGGLE = 'TASK_TOGGLE';

    /**
     * Attribute for delete action
     */
    public const DELETE = 'TASK_DELETE';

    /**
     * @var Security
     */
    private $security;

    public function __construct(Security $security)
    {
        $this->security = $security;
    }

    /**
     * Supports
     *
     * @param  string $attribute
     * @param  mixed $subject
     * @return bool True if the attribute is in the array and the subject is a Task instance, false otherwise
     */
    protected function supports(string $attribute, $subject): bool
    {
        return in_array($attribute, [self::EDIT, self::TOGGLE, self::DELETE])
            && $subject instanceof \App\Entity\Task;
    }

    /**
     * Perform a single access check operation on a given attribute, subject and token.
     *
     * @param  string $attribute
     * @param  mixed $task
     * @param  TokenInterface $token
     * @return bool True if access is allowed, false otherwise
     */
    protected function voteOnAttribute(string $attribute, $task, TokenInterface $token): bool
    {
        $user = $token->getUser();

        if (!$user instanceof UserInterface) {
            return false;
        }

        switch ($attribute) {
            case self::EDIT:
                return $this->canEdit($task, $user);
                break;
            case self::TOGGLE:
                return $this->canToggle($task, $user);
                break;
            case self::DELETE:
                return $this->canDelete($task, $user);
                break;
        }

        return false;
    }

    /**
     * Find out if it is possible to edit a task
     *
     * @param  Task $task Task entity
     * @param  UserInterface $user User interface
     * @return bool true if it is possible to edit, false otherwise
     */
    public function canEdit(Task $task, UserInterface $user): bool
    {
        return $task->getUser() === $user;
    }

    /**
     * Find out if it is possible to toggle a task
     *
     * @param  Task $task Task entity
     * @param  UserInterface $user User interface
     * @return bool true if it is possible to toggle, false otherwise
     */
    public function canToggle(Task $task, UserInterface $user): bool
    {
        return $task->getUser() === $user;
    }

    /**
     * Find out if it is possible to delete a task
     *
     * @param  Task $task Task entity
     * @param  UserInterface $user User interface
     * @return bool true if it is possible to delete, false otherwise
     */
    public function canDelete(Task $task, UserInterface $user): bool
    {
        return $this->security->isGranted('ROLE_ADMIN') && is_null($task->getUser()) || $task->getUser() === $user;
    }
}

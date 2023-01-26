<?php

namespace App\Service;

use App\Entity\User;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserService
{
    /**
     * @var ManagerRegistry
     */
    private $managerRegistry;

    /**
     * @var UserPasswordHasherInterface
     */
    private $passwordHasher;

    public function __construct(ManagerRegistry $managerRegistry, UserPasswordHasherInterface $passwordHasher)
    {
        $this->managerRegistry = $managerRegistry;
        $this->passwordHasher = $passwordHasher;
    }

    /**
     * CRUD task management
     *
     * @param  FormInterface $form user form
     * @param  User $user Entity user
     * @param  string $route_name Name of the route
     * @return bool True if the backup is successful, false otherwise
     */
    public function crudTaskManagement(FormInterface $form, User $user, string $route_name = 'user_create'): bool
    {
        $success = false;

        if ($form->isSubmitted() && $form->isValid()) {
            switch ($route_name) {
                case 'user_edit':
                    $this->updateUser($user);

                    break;
                default:
                    $this->createUser($user);

                    break;
            }

            $success = true;
        }

        return $success;
    }

    /**
     * Managing the modification of a user
     *
     * @return void
     */
    private function updateUser(User $user): void
    {
        $hashedPassword = $this->passwordHasher->hashPassword(
            $user,
            $user->getPassword()
        );
        $user->setPassword($hashedPassword);

        $this->managerRegistry->getManager()->flush();
    }

    /**
     * user backup management
     *
     * @param User $user Entity user
     * @return void
     */
    private function createUser(User $user): void
    {
        $entityManager = $this->managerRegistry->getManager();
        $hashedPassword = $this->passwordHasher->hashPassword(
            $user,
            $user->getPassword()
        );

        $user->setPassword($hashedPassword);

        $entityManager->persist($user);
        $entityManager->flush();
    }
}

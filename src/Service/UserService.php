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
    public function crudUserManagement(FormInterface $form, User $user, string $route_name = 'user_create'): bool
    {
        $success = false;
        if ($form->isSubmitted() && $form->isValid()) {
            $user_password = $form->get('user_password')->getData();

            switch ($route_name) {
                case 'user_edit':
                    $hashedPassword = $this->userPasswordManagement($user, $user_password, $route_name);
                    $this->updateUser($user, $hashedPassword);

                    break;
                default:
                    $hashedPassword = $this->userPasswordManagement($user, $user_password, $route_name);
                    $this->createUser($user, $hashedPassword);

                    break;
            }

            $success = true;
        }

        return $success;
    }

    /**
     * User password management
     *
     * @param  User $user User
     * @param  ?string $user_password User password
     * @param  string $route_name Name of the route
     *
     * @return string Password Hasher
     */
    private function userPasswordManagement(User $user, ?string $user_password, string $route_name): string
    {
        if ($route_name === 'user_edit' && is_null($user_password)) {
            return $user->getPassword();
        }

        return $this->passwordHasher->hashPassword(
            $user,
            $user_password
        );
    }

    /**
     * Managing the modification of a user
     *
     * @param  User $user User
     * @param  string $hashedPassword Password Hasher
     * @return void
     */
    private function updateUser(User $user, string $hashedPassword): void
    {
        $user->setPassword($hashedPassword);

        $this->managerRegistry->getManager()->flush();
    }

    /**
     * user backup management
     *
     * @param  User $user user
     * @param  string $hashedPassword Password Hasher
     * @return void
     */
    private function createUser(User $user, string $hashedPassword): void
    {
        $entityManager = $this->managerRegistry->getManager();

        $user->setPassword($hashedPassword);

        $entityManager->persist($user);
        $entityManager->flush();
    }
}

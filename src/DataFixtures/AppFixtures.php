<?php

namespace App\DataFixtures;

use DateTime;
use App\Entity\Task;
use App\Entity\User;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    private $passwordHasher;

    public function __construct(UserPasswordHasherInterface $passwordHasher)
    {
        $this->passwordHasher = $passwordHasher;
    }

    public function load(ObjectManager $manager): void
    {
        $this->createDatas($manager);
        $this->createTask($manager);
        $this->createTaskAnonymous($manager);
    }

    private function createTaskAnonymous(ObjectManager $manager)
    {
        $toggle = [
            true,
            false
        ];

        $task = new Task();

        $task
            ->setCreatedAt(new DateTime())
            ->setTitle("TitreAnonyme")
            ->setContent("Une tache anonyme")
            ->toggle(array_rand($toggle))
            ->setAuthor("Anonymous");

        $manager->persist($task);
        $manager->flush();
    }

    private function createTask(ObjectManager $manager)
    {
        $toggle = [
            '0' => true,
            '1' => false
        ];

        $user = $manager->getRepository(User::class)->findAll();

        for ($iterator = 0; $iterator <= 1; $iterator++) {
            $task = new Task();

            $task
                ->setCreatedAt(new DateTime())
                ->setTitle("Titre$iterator")
                ->setContent("Une tache n°$iterator")
                ->toggle($toggle[$iterator])
                ->setUser($user[0])
                ->setAuthor("Laurent");

            $manager->persist($task);
        }

        $manager->flush();
    }

    private function createDatas(ObjectManager $manager)
    {
        $roles = [
            '0' => ['ROLE' => 'ROLE_USER'],
            '1' => ['ROLE' => 'ROLE_ADMIN']
        ];

        for ($iterator = 0; $iterator <= 1; $iterator++) {
            $user = new User();

            $hashedPassword = $this->passwordHasher->hashPassword(
                $user,
                'password' . $iterator
            );

            $user
                ->setUsername("User$iterator")
                ->setPassword($hashedPassword)
                ->setEmail("User$iterator@gmail.com")
                ->setRoles($roles[$iterator]);

            $manager->persist($user);
        }

        $manager->flush();
    }
}

<?php

namespace tests\Controller\TestCase\Service\User;

use App\Entity\Task;
use App\Entity\User;
use App\Service\UserService;
use PHPUnit\Framework\TestCase;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasher;

class CrudUserManagementTest extends TestCase
{
    private const ROUTE_CREATE = "user_create";
    private const ROUTE_EDIT = "user_edit";

    /**
     * @var User
     */
    private $user;

    /**
     * @var Task
     */
    private $task;

    private $formInterface;

    private $entityManager;

    private $managerRegistry;

    private $passwordHasher;

    /**
     * @var UserService
     */
    private $userService;

    public function setUp(): void
    {
        $this->formInterface = $this->createMock(FormInterface::class);

        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->managerRegistry = $this->createMock(ManagerRegistry::class);
        $this->passwordHasher = $this->createMock(UserPasswordHasher::class);

        $this->userService = new UserService($this->managerRegistry, $this->passwordHasher);

        $this->task = new Task();
        $this->task
            ->setTitle('TitleTest')
            ->setContent('ContentTest')
            ->setCreatedAt(new \DateTime())
            ->setAuthor("AuthorTest")
            ->isDone(false)
        ;

        $this->user = new User();
        $this->user
            ->setEmail('MailTest@MailTest.fr')
            ->setPassword('PasswordTest')
            ->setRoles(['ROLE_USER'])
            ->setUsername('UsernameTest')
        ;
    }

    /**
     * test create successfull
     */
    public function testCrudUserManagementCreateSuccessfull()
    {
        $this->formInterface->method('isSubmitted')->willReturn(true);
        $this->formInterface->method('isValid')->willReturn(true);
        $this->formInterface->method('get')->with('user_password')->willReturn($this->formInterface);
        $this->formInterface->method('getData')->willReturn('PasswordTest');

        $this->passwordHasher
            ->method('hashPassword')
            ->with($this->user, $this->user->getPassword())
            ->willReturn('password_hasher')
        ;

        $this->managerRegistry
                ->expects($this->once())
                ->method('getManager')
                ->willReturn($this->entityManager);

        $this->entityManager
            ->expects($this->once())
            ->method('persist')
            ->with($this->user);

        $this->entityManager
            ->expects($this->once())
            ->method('flush');

        $this->assertTrue(
            $this->userService->crudUserManagement($this->formInterface, $this->user, self::ROUTE_CREATE)
        );

        $this->assertSame('password_hasher', $this->user->getPassword());
    }

    /**
     * test edit successfull
     */
    public function testCrudUserManagementEditSuccessfull()
    {
        $this->formInterface->method('isSubmitted')->willReturn(true);
        $this->formInterface->method('isValid')->willReturn(true);
        $this->formInterface->method('get')->with('user_password')->willReturn($this->formInterface);
        $this->formInterface->method('getData')->willReturn('PasswordTest');

        $this->passwordHasher
            ->method('hashPassword')
            ->with($this->user, $this->user->getPassword())
            ->willReturn('password_hasher')
        ;

        $this->managerRegistry
                ->expects($this->once())
                ->method('getManager')
                ->willReturn($this->entityManager);

        $this->entityManager
            ->expects($this->once())
            ->method('flush');

        $this->assertTrue(
            $this->userService->crudUserManagement($this->formInterface, $this->user, self::ROUTE_EDIT)
        );

        $this->assertSame('password_hasher', $this->user->getPassword());
    }

    /**
     * test edit password empty
     */
    public function testCrudUserManagementEditPasswordEmpty()
    {
        $this->formInterface->method('isSubmitted')->willReturn(true);
        $this->formInterface->method('isValid')->willReturn(true);
        $this->formInterface->method('get')->with('user_password')->willReturn($this->formInterface);
        $this->formInterface->method('getData')->willReturn(null);

        $this->managerRegistry
                ->expects($this->once())
                ->method('getManager')
                ->willReturn($this->entityManager);

        $this->entityManager
            ->expects($this->once())
            ->method('flush');

        $this->assertTrue(
            $this->userService->crudUserManagement($this->formInterface, $this->user, self::ROUTE_EDIT)
        );

        $this->assertSame('PasswordTest', $this->user->getPassword());
    }

    /**
     * test edit error is submitted
     */
    public function testCrudUserManagementEditErrorIsSubmitted()
    {
        $this->formInterface->method('isSubmitted')->willReturn(false);
        $this->formInterface->method('isValid')->willReturn(true);

        $this->assertFalse(
            $this->userService->crudUserManagement($this->formInterface, $this->user, self::ROUTE_EDIT)
        );
    }

    /**
     * test edit error is valid
     */
    public function testCrudUserManagementEditErrorIsValid()
    {
        $this->formInterface->method('isSubmitted')->willReturn(true);
        $this->formInterface->method('isValid')->willReturn(false);

        $this->assertFalse(
            $this->userService->crudUserManagement($this->formInterface, $this->user, self::ROUTE_EDIT)
        );
    }
}

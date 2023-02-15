<?php

namespace tests\Controller\TestCase\Service\Task;

use App\Entity\Task;
use App\Entity\User;
use App\Service\TaskService;
use PHPUnit\Framework\TestCase;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Form\FormInterface;

class CrudTaskManagementTest extends TestCase
{
    private const ROUTE_CREATE = "task_create";
    private const ROUTE_EDIT = "task_edit";

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

    /**
     * @var TaskService
     */
    private $taskService;

    public function setUp(): void
    {
        $this->formInterface = $this->createMock(FormInterface::class);

        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->managerRegistry = $this->createMock(ManagerRegistry::class);

        $this->taskService = new TaskService($this->managerRegistry);

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
    public function testCrudTaskManagementCreateSuccessfull()
    {
        $this->formInterface->method('isSubmitted')->willReturn(true);
        $this->formInterface->method('isValid')->willReturn(true);

        $this->managerRegistry
            ->expects($this->once())
            ->method('getManager')
            ->willReturn($this->entityManager);

        $this->entityManager
            ->expects($this->once())
            ->method('persist')
            ->with($this->task);

        $this->entityManager
            ->expects($this->once())
            ->method('flush');

        $this->assertTrue(
            $this->taskService->crudTaskManagement($this->formInterface, $this->task, $this->user, self::ROUTE_CREATE)
        );
    }

    /**
     * test edit successfull
     */
    public function testCrudTaskManagementEditSuccessfull()
    {
        $this->formInterface->method('isSubmitted')->willReturn(true);
        $this->formInterface->method('isValid')->willReturn(true);

        $this->managerRegistry
            ->expects($this->once())
            ->method('getManager')
            ->willReturn($this->entityManager);

        $this->entityManager
            ->expects($this->once())
            ->method('flush');

        $this->assertTrue(
            $this->taskService->crudTaskManagement($this->formInterface, $this->task, $this->user, self::ROUTE_EDIT)
        );
    }

    /**
     * test edit task with is valid false
     */
    public function testCrudTaskManagementEditErrorIsValid()
    {
        $this->formInterface->method('isSubmitted')->willReturn(true);
        $this->formInterface->method('isValid')->willReturn(false);

        $this->assertFalse(
            $this->taskService->crudTaskManagement($this->formInterface, $this->task, $this->user, self::ROUTE_EDIT)
        );
    }

    /**
     * test edit task with is submitted false
     */
    public function testCrudTaskManagementEditErrorIsSubmitted()
    {
        $this->formInterface->method('isSubmitted')->willReturn(false);
        $this->formInterface->method('isValid')->willReturn(true);

        $this->assertFalse(
            $this->taskService->crudTaskManagement($this->formInterface, $this->task, $this->user, self::ROUTE_EDIT)
        );
    }

    /**
     * test create task with is submitted false
     */
    public function testCrudTaskManagementCreateErrorIsSubmitted()
    {
        $this->formInterface->method('isSubmitted')->willReturn(false);
        $this->formInterface->method('isValid')->willReturn(true);

        $this->assertFalse(
            $this->taskService->crudTaskManagement($this->formInterface, $this->task, $this->user, self::ROUTE_CREATE)
        );
    }

    /**
     * test create task with is valid false
     */
    public function testCrudTaskManagementCreateErrorIsValid()
    {
        $this->formInterface->method('isSubmitted')->willReturn(true);
        $this->formInterface->method('isValid')->willReturn(false);

        $this->assertFalse(
            $this->taskService->crudTaskManagement($this->formInterface, $this->task, $this->user, self::ROUTE_CREATE)
        );
    }
}

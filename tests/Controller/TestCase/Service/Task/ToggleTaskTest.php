<?php

namespace tests\Controller\TestCase\Service\Task;

use App\Entity\Task;
use App\Service\TaskService;
use PHPUnit\Framework\TestCase;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;

class ToggleTaskTest extends TestCase
{
    /**
     * @var TaskService
     */
    private $taskService;

    private $entityManager;

    private $managerRegistry;

    /**
     * @var Task
     */
    private $task;

    public function setUp(): void
    {
        $this->task = new Task();

        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->managerRegistry = $this->createMock(ManagerRegistry::class);

        $this->taskService = new TaskService($this->managerRegistry);
    }

    /**
     * Test that an unfinished task becomes finished
     */
    public function testToggleTaskWithTaskUnfinishedBecomesFinished()
    {
        $this->task
            ->setTitle('TitleTest')
            ->setContent('ContentTest')
            ->setCreatedAt(new \DateTime())
            ->setAuthor("AuthorTest")
        ;

        $this->managerRegistry
            ->expects($this->once())
            ->method('getManager')
            ->willReturn($this->entityManager);

        $this->entityManager
            ->expects($this->once())
            ->method('flush');

        $this->taskService->toggleTask($this->task);

        $this->assertTrue($this->task->isDone());
    }

    /**
     * Test that a completed task becomes incomplete
     */
    public function testToggleTaskWithTaskCompletedBecomesIncomplete()
    {
        $this->task
            ->setTitle('TitleTest')
            ->setContent('ContentTest')
            ->setCreatedAt(new \DateTime())
            ->setAuthor("AuthorTest")
        ;

        $this->managerRegistry
            ->expects($this->exactly(2))
            ->method('getManager')
            ->willReturn($this->entityManager);

        $this->entityManager
            ->expects($this->exactly(2))
            ->method('flush');


        $this->taskService->toggleTask($this->task);
        $this->taskService->toggleTask($this->task);

        $this->assertFalse($this->task->isDone());
    }
}

<?php

namespace tests\Controller\TestCase\Service\Task;

use App\Service\TaskService;
use PHPUnit\Framework\TestCase;
use Doctrine\Persistence\ManagerRegistry;

class TypeTaskTest extends TestCase
{
    private const ENDED = "ended";
    private const PROGRESS = "progress";
    private const ALL = "all";
    /**
     * @var TaskService
     */
    private $taskService;

    public function setUp(): void
    {
        $managerRegistry = $this->createMock(ManagerRegistry::class);
        $this->taskService = new TaskService($managerRegistry);
    }

    /**
     * Test the url parameter for a completed task
     */
    public function testGetTypeTaskParameterEnded()
    {
        $type_task = $this->taskService->getTypeTask(self::ENDED);

        $this->assertSame('terminée', $type_task);
    }

    /**
     * Test the url parameter for an ongoing task
     */
    public function testGetTypeTaskParameterProgress()
    {
        $type_task = $this->taskService->getTypeTask(self::PROGRESS);

        $this->assertSame('en cours', $type_task);
    }

    /**
     * Test the url parameter for all tasks
     */
    public function testGetTypeTaskParameterAll()
    {
        $type_task = $this->taskService->getTypeTask(self::ALL);

        $this->assertSame('', $type_task);
    }

    /**
     * Test url parameter for all tasks with wrong parameter
     */
    public function testGetTypeTaskParameterWrong()
    {
        $type_task = $this->taskService->getTypeTask('WrongParameter');

        $this->assertSame('', $type_task);
    }
}

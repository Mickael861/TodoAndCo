<?php

namespace tests\Controller\TestCase\Voter;

use App\Entity\Task;
use App\Entity\User;
use Monolog\Test\TestCase;
use App\Security\Voter\TaskVoter;
use Symfony\Component\Security\Core\Security;

class TaskVoterTest extends TestCase
{
    /**
     * @var TaskVoter
     */
    private $taskVoter;

    /**
     * @var User
     */
    private $user;

    /**
     * @var Task
     */
    private $task;

    /**
     * @var User
     */
    private $badUser;

    /**
     * @var Task
     */
    private $taskAnonymous;

    public function setUp(): void
    {
        $security = $this->createMock(Security::class);

        $this->taskVoter = new TaskVoter($security);

        $this->user = new User();
        $this->user
            ->setEmail('MailTest@MailTest.fr')
            ->setPassword('PasswordTest')
            ->setRoles(['ROLE_USER'])
            ->setUsername('UsernameTest')
        ;

        $this->badUser = new User();
        $this->badUser
            ->setEmail('MailTest@MailTest.fr')
            ->setPassword('PasswordTest')
            ->setRoles(['ROLE_USER'])
            ->setUsername('UsernameTest')
        ;

        $this->task = new Task();
        $this->task
            ->setTitle('TitleTest')
            ->setContent('ContentTest')
            ->setCreatedAt(new \DateTime())
            ->setAuthor("AuthorTest")
            ->isDone(false)
        ;

        $this->taskAnonymous = new Task();
        $this->taskAnonymous
            ->setTitle('TitleTest')
            ->setContent('ContentTest')
            ->setCreatedAt(new \DateTime())
            ->setAuthor("AuthorTest")
            ->isDone(false)
        ;
    }

    /**
     * Test if the user can't edit
     */
    public function testCanEditOk()
    {
        $this->task->setUser($this->user);

        $this->assertTrue($this->taskVoter->canEdit($this->task, $this->user));
    }

    /**
     * Test if the user can edit
     */
    public function testCanEditKo()
    {
        $this->task->setUser($this->user);

        $this->assertFalse($this->taskVoter->canEdit($this->task, $this->badUser));
    }

    /**
     * Test if the user can toggle
     */
    public function testCanToggleOk()
    {
        $this->task->setUser($this->user);

        $this->assertTrue($this->taskVoter->canToggle($this->task, $this->user));
    }

    /**
     * Test if the user can't toggle
     */
    public function testCanToggleKo()
    {
        $this->task->setUser($this->user);

        $this->assertFalse($this->taskVoter->canToggle($this->task, $this->badUser));
    }

    /**
     * Test if the user can delete
     */
    public function testCanDeleteTaskWithGoodUserOk()
    {
        $this->task->setUser($this->user);

        $this->assertTrue($this->taskVoter->canDelete($this->task, $this->user));
    }

    /**
     * Test if the user can't delete
     */
    public function testCanDeleteTaskWithGoodUserKo()
    {
        $this->task->setUser($this->user);

        $this->assertFalse($this->taskVoter->canDelete($this->task, $this->badUser));
    }

    /**
     * Test if the user can delete task anonymous
     */
    public function testCanDeleteTaskAnonymousWithRoleAdminOk()
    {
        $this->user->setRoles(['ROLE_ADMIN']);

        $this->assertTrue($this->taskVoter->canDelete($this->taskAnonymous, $this->user));
    }

    /**
     * Test if the user can't delete task anonymous
     */
    public function testCanDeleteTaskAnonymousWithRoleAdminKo()
    {
        $this->assertFalse($this->taskVoter->canDelete($this->taskAnonymous, $this->badUser));
    }
}

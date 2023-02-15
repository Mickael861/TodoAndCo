<?php

namespace tests\Controller\WebTestCase\task;

use App\Entity\Task;
use App\Entity\User;
use App\TestsHelper\WebTestCaseHelper;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class TaskEditTest extends WebTestCase
{
    /**
     * @var KernelBrowser
     */
    private $client;

    /**
     * @var object|null
     */
    private $urlGenerator;

    /**
     * @var User
     */
    private $user;

    /**
     * @var Task
     */
    private $task;

    /**
     * @var WebTestCaseHelper
     */
    private $webTestCaseHelper;

    public function setUp(): void
    {
        $this->client = static::createClient();

        $this->urlGenerator = $this->client->getContainer()->get('router.default');

        $this->webTestCaseHelper = new WebTestCaseHelper($this->client, $this->urlGenerator);

        $this->user = $this->webTestCaseHelper->getEntity(User::class, 'findByUsername', 'user0');

        $this->task = $this->webTestCaseHelper->getEntity(Task::class, 'findByTitle', 'Titre0');
    }

    /**
     * Check the status code listAction
     */
    public function testEditActionUserNoLogged()
    {
        $this->webTestCaseHelper->getClientRequest('task_edit', ['id' => $this->task->getId()]);

        $this->client->followRedirect();

        $this->assertSelectorTextContains('h1', "S'identifier");
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
    }

    /**
     * Verification of url for edit task
     */
    public function testEditActionUserLogged()
    {
        $this->client->loginUser($this->user);

        $this->webTestCaseHelper->getClientRequest('task_edit', ['id' => $this->task->getId()]);

        $this->assertSelectorTextContains('h1', "Modification d'une tâche");
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
    }

    /**
     * test the click on the task list button
     */
    public function testEditActionBtnTaskList()
    {
        $this->client->loginUser($this->user);

        $crawler = $this->webTestCaseHelper->getClientRequest('task_edit', ['id' => $this->task->getId()]);

        $this->webTestCaseHelper->setLinkClick($crawler, 'Retour à la liste des tâches');

        $this->assertSelectorTextContains(
            'h1',
            "Liste des tâches"
        );
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
    }

    /**
     * Test the edit of a field value task
     */
    public function testEditActionValueTask()
    {
        $this->client->loginUser($this->user);

        $this->webTestCaseHelper->getClientRequest('task_edit', ['id' => $this->task->getId()]);

        $this->assertInputValueSame("task[title]", $this->task->getTitle());
        $this->assertSelectorTextContains("textarea#task_content", $this->task->getContent());
        $this->assertInputValueSame("task[author]", $this->task->getAuthor());
        $this->assertSelectorTextContains('h1', "Modification d'une tâche");
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
    }

    /**
     * Test the edit of a task successfull
     */
    public function testEditActionEditTaskSuccessfull()
    {
        $this->client->loginUser($this->user);

        $crawler = $this->webTestCaseHelper->getClientRequest('task_edit', ['id' => $this->task->getId()]);

        $this->webTestCaseHelper->submitForm($crawler, 'btn-form', [
            'task[title]' => 'title2',
            'task[content]' => 'content2'
        ]);

        $this->client->followRedirect();

        $task = $this->webTestCaseHelper->getEntity(Task::class, 'findBy', [
            "title" => "title2",
            "content" => "content2",
            "author" => "Laurent"
        ]);

        $this->assertNotEmpty($task);
        $this->assertSelectorTextContains(
            'div.alert.alert-success',
            "Superbe ! La tâche a bien été modifiée."
        );
        $this->assertSelectorTextContains('h1', "Liste des tâches terminée");
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
    }

    /**
     * Test the edit of a task error
     */
    public function testEditActionEditTaskError()
    {
        $this->client->loginUser($this->user);

        $crawler = $this->webTestCaseHelper->getClientRequest('task_edit', ['id' => $this->task->getId()]);

        $this->webTestCaseHelper->submitForm($crawler, 'btn-form', [
            'task[title]' => '',
            'task[content]' => ''
        ]);

        $this->assertSelectorTextContains("input#task_title ~ .invalid-feedback", "Vous devez saisir un titre.");
        $this->assertSelectorTextContains("textarea#task_content ~ .invalid-feedback", "Vous devez saisir du contenu.");
        $this->assertSelectorTextContains('h1', "Modification d'une tâche");
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
    }

    /**
     * Test the edit of a task author error
     */
    public function testEditActionModifyTaskAuthor()
    {
        $this->client->loginUser($this->user);

        $crawler = $this->webTestCaseHelper->getClientRequest('task_edit', ['id' => $this->task->getId()]);

        $form = $crawler->selectButton('btn-form')->form([
            'task[author]' => 'author fail'
        ]);
        $this->client->submit($form);

        $this->client->followRedirect();

        $task = $this->webTestCaseHelper->getEntity(Task::class, 'findBy', [
            "title" => $this->task->getTitle(),
            "content" => $this->task->getContent(),
            "author" => $this->task->getAuthor()
        ]);

        $this->assertNotEmpty($task);
        $this->assertSelectorTextContains(
            'div.alert.alert-success',
            "Superbe ! La tâche a bien été modifiée."
        );
        $this->assertSelectorTextContains('h1', "Liste des tâches terminée");
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
    }
}

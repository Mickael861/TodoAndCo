<?php

namespace tests\Controller\WebTestCase\task;

use App\Entity\Task;
use App\Entity\User;
use App\TestsHelper\WebTestCaseHelper;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class TaskCreateTest extends WebTestCase
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
     * @var WebTestCaseHelper
     */
    private $webTestCaseHelper;

    public function setUp(): void
    {
        $this->client = static::createClient();

        $this->urlGenerator = $this->client->getContainer()->get('router.default');

        $this->webTestCaseHelper = new WebTestCaseHelper($this->client, $this->urlGenerator);

        $this->user = $this->webTestCaseHelper->getEntity(User::class, 'findByUsername', 'user0');
    }

        /**
     * Check the status code listAction
     */
    public function testCreateActionUserNoLogged()
    {
        $this->webTestCaseHelper->getClientRequest('task_create');

        $this->client->followRedirect();

        $this->assertSelectorTextContains('h1', "S'identifier");
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
    }

    /**
     * Verification of different urls for all tasks
     */
    public function testCreateActionUserLogged()
    {
        $this->client->loginUser($this->user);

        $this->webTestCaseHelper->getClientRequest('task_create');

        $this->assertSelectorTextContains('h1', "Création d'une tâche");
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
    }

    /**
     * test the click on the task list button
     */
    public function testCreateActionBtnTaskList()
    {
        $this->client->loginUser($this->user);

        $crawler = $this->webTestCaseHelper->getClientRequest('task_create');

        $this->webTestCaseHelper->setLinkClick($crawler, 'Retour à la liste des tâches');

        $this->assertSelectorTextContains(
            'h1',
            "Liste des tâches"
        );
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
    }

    /**
     * Test the creation of a task successfull
     */
    public function testCreateActionCreateTaskSuccessfull()
    {
        $this->client->loginUser($this->user);

        $crawler = $this->webTestCaseHelper->getClientRequest('task_create');

        $this->webTestCaseHelper->submitForm($crawler, 'btn-form', [
            'task[title]' => 'title1',
            'task[content]' => 'content1',
            'task[author]' => 'author1'
        ]);

        $this->client->followRedirect();

        $task = $this->webTestCaseHelper->getEntity(Task::class, 'findBy', [
            "title" => "title1",
            "content" => "content1",
            "author" => "author1"
        ]);

        $this->assertNotEmpty($task);
        $this->assertSelectorTextContains(
            'div.alert.alert-success',
            "Superbe ! La tâche a été bien été ajoutée."
        );
        $this->assertSelectorTextContains('h1', "Liste des tâches en cours");
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
    }

    /**
     * Test the creation of a task error
     */
    public function testCreateActionCreateTaskError()
    {
        $this->client->loginUser($this->user);

        $crawler = $this->webTestCaseHelper->getClientRequest('task_create');

        $this->webTestCaseHelper->submitForm($crawler, 'btn-form', [
            'task[title]' => '',
            'task[content]' => '',
            'task[author]' => ''
        ]);

        $this->assertSelectorTextContains("input#task_title ~ .invalid-feedback", "Vous devez saisir un titre.");
        $this->assertSelectorTextContains("textarea#task_content ~ .invalid-feedback", "Vous devez saisir du contenu.");
        $this->assertSelectorTextContains("input#task_author ~ .invalid-feedback", "Vous devez saisir un auteur.");
        $this->assertSelectorTextContains('h1', "Création d'une tâche");
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
    }
}

<?php

namespace tests\Controller\WebTestCase\task;

use App\Entity\User;
use App\TestsHelper\WebTestCaseHelper;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class TaskListTest extends WebTestCase
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
     * @var User
     */
    private $admin;

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
        $this->admin = $this->webTestCaseHelper->getEntity(User::class, 'findByUsername', 'user1');
    }

    /**
     * Check the status code listAction
     */
    public function testListActionUserNoLogged()
    {
        $this->webTestCaseHelper->getClientRequest('task_list', ['is_done' => 'all']);

        $this->client->followRedirect();

        $this->assertSelectorTextContains('h1', "S'identifier");
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
    }

    /**
     * Verification of different urls for all tasks
     */
    public function testListActionUserLoggedTaskAll()
    {
        $this->client->loginUser($this->user);

        $this->webTestCaseHelper->getClientRequest('task_list', ['is_done' => 'all']);

        $this->assertSelectorTextContains('h1', "Liste des tâches");
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
    }

    /**
     * Verification of different urls for ended tasks
     */
    public function testListActionUserLoggedTaskEnded()
    {
        $this->client->loginUser($this->user);

        $this->webTestCaseHelper->getClientRequest('task_list', ['is_done' => 'ended']);

        $this->assertSelectorTextContains('h1', "Liste des tâches terminée");
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
    }

    /**
     * Check the status code progress tasks
     */
    public function testListActionUserLoggedTaskProgress()
    {
        $this->client->loginUser($this->user);

        $this->webTestCaseHelper->getClientRequest('task_list', ['is_done' => 'progress']);

        $this->assertSelectorTextContains('h1', "Liste des tâches en cours");
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
    }

    /**
     * test the click on the home button
     */
    public function testListActionBtnHome()
    {
        $this->client->loginUser($this->user);

        $crawler = $this->webTestCaseHelper->getClientRequest('task_list', ['is_done' => 'all']);

        $this->webTestCaseHelper->setLinkClick($crawler, 'Accueil');

        $this->assertSelectorTextContains(
            'h1',
            "Bienvenue sur Todo List, l'application vous permettant de gérer l'ensemble de vos tâches sans effort !"
        );
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
    }

    /**
     * test the click on the button of creation of a task
     */
    public function testListActionBtnCreateTask()
    {
        $this->client->loginUser($this->user);

        $crawler = $this->webTestCaseHelper->getClientRequest('task_list', ['is_done' => 'all']);

        $this->webTestCaseHelper->setLinkClick($crawler, 'Créer une tâche');

        $this->assertSelectorTextContains('h1', "Création d'une tâche");
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
    }

    /**
     * test the click on the button of edit of a task
     */
    public function testListActionBtnEditTask()
    {
        $this->client->loginUser($this->user);

        $crawler = $this->webTestCaseHelper->getClientRequest('task_list', ['is_done' => 'all']);

        $this->webTestCaseHelper->setLinkClick($crawler, 'Titre0');

        $this->assertSelectorTextContains('h1', "Modification d'une tâche");
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
    }

    /**
     * test the click on the button of toggle ended of a task
     */
    public function testListActionBtnToggleEndedTask()
    {
        $this->client->loginUser($this->user);

        $this->webTestCaseHelper->submitFormTaskIdetifier(
            'task_list',
            ['is_done' => 'ended'],
            "Titre0",
            "findByTitle",
            "btn-toggle"
        );

        $this->client->followRedirect();

        $this->assertSelectorTextContains(
            'div.alert.alert-success',
            "Superbe ! La tâche Titre0 a bien été marquée comme non terminée."
        );
        $this->assertSelectorTextContains('h1', "Liste des tâches en cours");
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
    }

    /**
     * test the click on the button of toggle progress of a task
     */
    public function testListActionBtnToggleProgressTask()
    {
        $this->client->loginUser($this->admin);

        $this->webTestCaseHelper->submitFormTaskIdetifier(
            'task_list',
            ['is_done' => 'progress'],
            "Titre1",
            "findByTitle",
            "btn-toggle"
        );

        $this->client->followRedirect();

        $this->assertSelectorTextContains(
            'div.alert.alert-success',
            "Superbe ! La tâche Titre1 a bien été marquée comme faite."
        );
        $this->assertSelectorTextContains('h1', "Liste des tâches terminée");
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
    }

    /**
     * test the click on the button of delete of a task
     */
    public function testListActionBtnDeleteTask()
    {
        $this->client->loginUser($this->user);

        $this->webTestCaseHelper->submitFormTaskIdetifier(
            'task_list',
            ['is_done' => 'all'],
            "Titre0",
            "findByTitle",
            "btn-delete"
        );

        $this->client->followRedirect();

        $this->assertSelectorTextContains(
            'div.alert.alert-success',
            "Superbe ! La tâche a bien été supprimée."
        );
        $this->assertSelectorTextContains('h1', "Liste des tâches terminée");
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
    }
}

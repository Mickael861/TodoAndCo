<?php

namespace Tests\Controller\WebTestCase\task;

use App\Entity\Task;
use App\Entity\User;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\HttpFoundation\Request;
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

    public function setUp(): void
    {
        $this->client = static::createClient();

        $this->urlGenerator = $this->client->getContainer()->get('router.default');

        $userRepository = $this->client->getContainer()->get('doctrine.orm.entity_manager')->getRepository(User::class);
        $this->user = $userRepository->findByUsername("user0")[0];
    }

        /**
     * Check the status code listAction
     */
    public function testCreateActionUserNoLogged()
    {
        $this->getClientRequestTaskCreate();

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

        $this->getClientRequestTaskCreate();

        $this->assertSelectorTextContains('h1', "Création d'une tâche");
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
    }

    /**
     * test the click on the home button
     */
    public function testCreateActionBtnHome()
    {
        $this->client->loginUser($this->user);

        $crawler = $this->getClientRequestTaskCreate();

        $this->setLinkClick($crawler, 'Accueil');

        $this->assertSelectorTextContains(
            'h1',
            "Bienvenue sur Todo List, l'application vous permettant de gérer l'ensemble de vos tâches sans effort !"
        );
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
    }

    /**
     * test the click on the task list button
     */
    public function testCreateActionBtnTaskList()
    {
        $this->client->loginUser($this->user);

        $crawler = $this->getClientRequestTaskCreate();

        $this->setLinkClick($crawler, 'Retour à la liste des tâches');

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

        $crawler = $this->getClientRequestTaskCreate();

        $this->setDatasFormSubmitCreateTask($crawler, 'title1', 'content1', 'author1');

        $this->client->followRedirect();

        $taskRepository = $this->client->getContainer()->get('doctrine.orm.entity_manager')->getRepository(Task::class);
        $task = $taskRepository->findByTitle([
            "title" => "title1",
            "content" => "content1",
            "author" => "author1"
        ])[0];

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

        $crawler = $this->getClientRequestTaskCreate();

        $this->setDatasFormSubmitCreateTask($crawler);

        $this->assertSelectorTextContains("input#task_title ~ .invalid-feedback", "Vous devez saisir un titre.");
        $this->assertSelectorTextContains("textarea#task_content ~ .invalid-feedback", "Vous devez saisir du contenu.");
        $this->assertSelectorTextContains("input#task_author ~ .invalid-feedback", "Vous devez saisir un auteur.");
        $this->assertSelectorTextContains('h1', "Création d'une tâche");
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
    }


    /**
     * Fill out the task creation form
     *
     * @param Crawler $crawler Crawler
     * @param string $title Title of the task
     * @param string $content Content of the task
     * @param string $author Author of the task
     * @return void
     */
    private function setDatasFormSubmitCreateTask(
        Crawler $crawler,
        string $title = '',
        string $content = '',
        string $author = ''
    ): void {
        $form = $crawler->selectButton('btn-form')->form([
            'task[title]' => $title,
            'task[content]' => $content,
            'task[author]' => $author
        ]);

        $this->client->submit($form);
    }

    /**
     * Add a click on a link
     *
     * @param  Crawler $crawler Crawler
     * @param  string $text_link Textual content of the link
     * @return void
     */
    private function setLinkClick(Crawler $crawler, string $text_link): void
    {
        $link = $crawler->selectLink($text_link)->link();
        $this->client->click($link);
    }

    /**
     * Retrieve the crawler from the task create
     *
     * @return Crawler
     */
    private function getClientRequestTaskCreate(): Crawler
    {
        return $this->client->request(
            Request::METHOD_GET,
            $this->urlGenerator->generate('task_create')
        );
    }
}

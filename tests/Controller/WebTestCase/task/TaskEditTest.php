<?php

namespace Tests\Controller\WebTestCase\task;

use App\Entity\Task;
use App\Entity\User;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\HttpFoundation\Request;
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

    public function setUp(): void
    {
        $this->client = static::createClient();

        $this->urlGenerator = $this->client->getContainer()->get('router.default');

        $repository = $this->client->getContainer()->get('doctrine.orm.entity_manager');

        $userRepository = $repository->getRepository(User::class);
        $this->user = $userRepository->findByUsername("user0")[0];

        $taskRepository = $repository->getRepository(Task::class);
        $this->task = $taskRepository->findByTitle("Titre0")[0];
    }

    /**
     * Check the status code listAction
     */
    public function testEditActionUserNoLogged()
    {
        $this->getClientRequestTaskEdit();

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

        $this->getClientRequestTaskEdit();

        $this->assertSelectorTextContains('h1', "Modification d'une tâche");
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
    }

    /**
     * test the click on the home button
     */
    public function testEditActionBtnHome()
    {
        $this->client->loginUser($this->user);

        $crawler = $this->getClientRequestTaskEdit();

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
    public function testEditActionBtnTaskList()
    {
        $this->client->loginUser($this->user);

        $crawler = $this->getClientRequestTaskEdit();

        $this->setLinkClick($crawler, 'Retour à la liste des tâches');

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

        $this->getClientRequestTaskEdit();

        $this->assertInputValueSame("task[title]", "Titre0");
        $this->assertSelectorTextContains("textarea#task_content", "Une tache n°0");
        $this->assertInputValueSame("task[author]", "Laurent");
        $this->assertSelectorTextContains('h1', "Modification d'une tâche");
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
    }

    /**
     * Test the edit of a task successfull
     */
    public function testEditActionEditTaskSuccessfull()
    {
        $this->client->loginUser($this->user);

        $crawler = $this->getClientRequestTaskEdit();

        $this->setDatasFormSubmitCreateTask($crawler, 'title2', 'content2');

        $this->client->followRedirect();

        $taskRepository = $this->client->getContainer()->get('doctrine.orm.entity_manager')->getRepository(Task::class);
        $task = $taskRepository->findBy([
            "title" => "title2",
            "content" => "content2",
            "author" => "Laurent"
        ])[0];

        $this->assertNotEmpty($task);
        $this->assertSelectorTextContains(
            'div.alert.alert-success',
            "Superbe ! La tâche a bien été modifiée."
        );
        $this->assertSelectorTextContains('h1', "Liste des tâches terminées");
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
    }

    /**
     * Test the edit of a task error
     */
    public function testEditActionEditTaskError()
    {
        $this->client->loginUser($this->user);

        $crawler = $this->getClientRequestTaskEdit();

        $this->setDatasFormSubmitCreateTask($crawler);

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

        $crawler = $this->getClientRequestTaskEdit();

        $form = $crawler->selectButton('btn-form')->form([
            'task[author]' => 'author fail'
        ]);
        $this->client->submit($form);

        $this->client->followRedirect();

        $taskRepository = $this->client->getContainer()->get('doctrine.orm.entity_manager')->getRepository(Task::class);
        $task = $taskRepository->findBy([
            "title" => $this->task->getTitle(),
            "content" => $this->task->getContent(),
            "author" => $this->task->getAuthor()
        ])[0];

        $this->assertNotEmpty($task);
        $this->assertSelectorTextContains(
            'div.alert.alert-success',
            "Superbe ! La tâche a bien été modifiée."
        );
        $this->assertSelectorTextContains('h1', "Liste des tâches terminées");
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
        string $content = ''
    ): void {
        $form = $crawler->selectButton('btn-form')->form([
            'task[title]' => $title,
            'task[content]' => $content
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
     * Retrieve the crawler from the task edit
     *
     * @return Crawler
     */
    private function getClientRequestTaskEdit(): Crawler
    {
        return $this->client->request(
            Request::METHOD_GET,
            $this->urlGenerator->generate('task_edit', ['id' => $this->task->getId()])
        );
    }
}

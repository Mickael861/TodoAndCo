<?php

namespace Tests\Controller\WebTestCase\task;

use App\Entity\Task;
use App\Entity\User;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
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
    public function testListActionUserNoLogged()
    {
        $this->getClientRequestTaskList(['is_done' => 'all']);

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

        $this->getClientRequestTaskList(['is_done' => 'all']);

        $this->assertSelectorTextContains('h1', "Liste des tâches");
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
    }

    /**
     * Verification of different urls for ended tasks
     */
    public function testListActionUserLoggedTaskEnded()
    {
        $this->client->loginUser($this->user);

        $this->getClientRequestTaskList(['is_done' => 'ended']);

        $this->assertSelectorTextContains('h1', "Liste des tâches terminée");
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
    }

    /**
     * Check the status code progress tasks
     */
    public function testListActionUserLoggedTaskProgress()
    {
        $this->client->loginUser($this->user);

        $this->getClientRequestTaskList(['is_done' => 'progress']);

        $this->assertSelectorTextContains('h1', "Liste des tâches en cours");
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
    }

    /**
     * test the click on the home button
     */
    public function testListActionBtnHome()
    {
        $this->client->loginUser($this->user);

        $crawler = $this->getClientRequestTaskList(['is_done' => 'all']);

        $this->setLinkClick($crawler, 'Accueil');

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

        $crawler = $this->getClientRequestTaskList(['is_done' => 'all']);

        $this->setLinkClick($crawler, 'Créer une tâche');

        $this->assertSelectorTextContains('h1', "Création d'une tâche");
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
    }

    /**
     * test the click on the button of edit of a task
     */
    public function testListActionBtnEditTask()
    {
        $this->client->loginUser($this->user);

        $crawler = $this->getClientRequestTaskList(['is_done' => 'all']);

        $this->setLinkClick($crawler, 'Titre0');

        $this->assertSelectorTextContains('h1', "Modification d'une tâche");
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
    }

    /**
     * test the click on the button of toggle ended of a task
     */
    public function testListActionBtnToggleEndedTask()
    {
        $this->client->loginUser($this->user);

        $crawler = $this->getClientRequestTaskList(['is_done' => 'ended']);

        $id_task = $this->getTitleTaskId("Titre0");

        $this->submitForm($crawler, "btn-toggle-$id_task");

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
        $this->client->loginUser($this->user);

        $crawler = $this->getClientRequestTaskList(['is_done' => 'progress']);

        $id_task = $this->getTitleTaskId("Titre1");

        $this->submitForm($crawler, "btn-toggle-$id_task");

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

        $crawler = $this->getClientRequestTaskList(['is_done' => 'all']);

        $id_task = $this->getTitleTaskId("Titre0");

        $this->submitForm($crawler, "btn-delete-$id_task");

        $this->client->followRedirect();

        $this->assertSelectorTextContains(
            'div.alert.alert-success',
            "Superbe ! La tâche a bien été supprimée."
        );
        $this->assertSelectorTextContains('h1', "Liste des tâches terminées");
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
    }

    /**
     * Retrieve the crawler from the task list
     *
     * @param array $parameter URL parameter
     * @return Crawler
     */
    private function getClientRequestTaskList(array $parameter = []): Crawler
    {
        return $this->client->request(
            Request::METHOD_GET,
            $this->urlGenerator->generate('task_list', $parameter)
        );
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
     * Retrieve the identifier of a task in relation to its title
     *
     * @param  string $title_name name of the title task
     * @return int Identifier task
     */
    private function getTitleTaskId(string $title_name): int
    {
        $taskRepository = $this->client->getContainer()->get('doctrine.orm.entity_manager')->getRepository(Task::class);
        return $taskRepository->findByTitle($title_name)[0]->getId();
    }

    /**
     * submit a form
     *
     * @param  Crawler $crawler Crawler
     * @param  string $selecter Selector of the button form
     * @return void
     */
    private function submitForm(Crawler $crawler, string $selector): void
    {
        $form = $crawler->selectButton($selector)->form([]);
        $this->client->submit($form);
    }
}

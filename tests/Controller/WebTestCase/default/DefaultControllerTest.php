<?php

namespace tests\Controller\WebTestCase\homepage;

use App\Entity\User;
use App\TestsHelper\WebTestCaseHelper;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class DefaultControllerTest extends WebTestCase
{
    /**
     * @var KernelBrowser
     */
    private $client;

    /**
     * @var Router
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
     * Test the redirect on the login page if no user is logged in
     */
    public function testIndexNoUserLoggedRedirectLoginStatusCodeOK()
    {
        $this->client->request(Request::METHOD_GET, $this->urlGenerator->generate('homepage'));

        $this->client->followRedirect();

        $this->assertSelectorTextContains('h1', "S'identifier");
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
    }

    /**
     * Test the redirect on the login page if user is logged in
     */
    public function testIndexUserLoggedNoRedirectLoginStatusCodeOK()
    {
        $this->client->loginUser($this->user);

        $this->client->request(Request::METHOD_GET, $this->urlGenerator->generate('homepage'));

        $this->assertSelectorTextContains(
            'h1',
            "Bienvenue sur Todo List, l'application vous permettant de gérer l'ensemble de vos tâches sans effort !"
        );
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
    }

    /**
     * Test button to show all tasks
     */
    public function testIndexButtonAllTasks()
    {
        $this->client->loginUser($this->user);

        $crawler = $this->client->request(Request::METHOD_GET, $this->urlGenerator->generate('homepage'));

        $link = $crawler->selectLink('Consulter toutes les tâches')->link();
        $this->client->click($link);

        $this->assertSelectorTextContains('h1', "Liste des tâches");
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
    }

    /**
     * Test button to show ended tasks
     */
    public function testIndexButtonEndedTasks()
    {
        $this->client->loginUser($this->user);

        $crawler = $this->client->request(Request::METHOD_GET, $this->urlGenerator->generate('homepage'));

        $link = $crawler->selectLink('Consulter la liste des tâches terminées')->link();
        $this->client->click($link);

        $this->assertSelectorTextContains('h1', "Liste des tâches terminées");
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
    }

    /**
     * Test button to show progress tasks
     */
    public function testIndexButtonProgressTasks()
    {
        $this->client->loginUser($this->user);

        $crawler = $this->client->request(Request::METHOD_GET, $this->urlGenerator->generate('homepage'));

        $link = $crawler->selectLink('Consulter la liste des tâches à faire')->link();
        $this->client->click($link);

        $this->assertSelectorTextContains('h1', "Liste des tâches en cours");
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
    }

    /**
     * Test button to show create tasks
     */
    public function testIndexButtonCreateTasks()
    {
        $this->client->loginUser($this->user);

        $crawler = $this->client->request(Request::METHOD_GET, $this->urlGenerator->generate('homepage'));

        $link = $crawler->selectLink('Créer une nouvelle tâche')->link();
        $this->client->click($link);

        $this->assertSelectorTextContains('h1', "Création d'une tâche");
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
    }
}

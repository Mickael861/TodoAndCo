<?php

namespace Tests\Controller\WebTestCase;

use App\Entity\User;
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

    public function setUp(): void
    {
        $this->client = static::createClient();

        $this->urlGenerator = $this->client->getContainer()->get('router.default');
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
        $this->loginUser();

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
        $this->loginUser();

        $crawler = $this->client->request(Request::METHOD_GET, $this->urlGenerator->generate('homepage'));

        $link = $crawler->selectLink('Consulter toutes les tâches')->link();
        $this->client->click($link);

        $this->assertSelectorTextContains('h1', "Liste des tâches faites");
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
    }

    /**
     * Test button to show ended tasks
     */
    public function testIndexButtonEndedTasks()
    {
        $this->loginUser();

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
        $this->loginUser();

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
        $this->loginUser();

        $crawler = $this->client->request(Request::METHOD_GET, $this->urlGenerator->generate('homepage'));

        $link = $crawler->selectLink('Créer une nouvelle tâche')->link();
        $this->client->click($link);

        $this->assertSelectorTextContains('h1', "Création d'une tâche");
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
    }

    /**
     * Login user
     */
    private function loginUser()
    {
        $userRepository = $this->client->getContainer()->get('doctrine.orm.entity_manager')->getRepository(User::class);
        $user = $userRepository->findByUsername("User0");
        $this->client->loginUser($user[0]);
    }
}

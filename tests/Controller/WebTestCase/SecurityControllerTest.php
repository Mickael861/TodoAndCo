<?php

namespace Tests\Controller;

use App\Entity\User;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class SecurityControllerTest extends WebTestCase
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
     * check that the application redirects to the tasks page if the user is logged in
     */
    public function testLoginActionRedirectTasksUserLoggedStatusCodeOK()
    {
        $this->client->loginUser($this->user);

        $this->client->request(Request::METHOD_GET, $this->urlGenerator->generate('login'));

        $this->client->followRedirect();

        $this->assertSelectorTextContains('.alert.alert-danger', "Oops ! Vous êtes déjà connecté");
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
    }

    /**
     * Check the status code loginAction
     */
    public function testLoginActionUserNoLoggedStatusCodeOK()
    {
        $this->client->request(Request::METHOD_GET, $this->urlGenerator->generate('login'));

        $this->assertSelectorTextContains('h1', "S'identifier");
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
    }

    /**
     * Verify that authentication successfull
     */
    public function testLoginActionAuthSuccessfull()
    {
        $crawler = $this->client->request(Request::METHOD_GET, $this->urlGenerator->generate('login'));

        $form = $crawler->selectButton('btn-form')->form([
            '_username' => 'user0',
            '_password' => 'password0'
        ]);

        $this->client->submit($form);

        $this->client->followRedirect();

        $content_title =
            "Bienvenue sur Todo List, l'application vous permettant de gérer l'ensemble de vos tâches sans effort !";

        $this->assertSelectorTextContains('h1', $content_title);
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
    }

    /**
     * Verify that authentication failed
     */
    public function testLoginActionAuthFailed()
    {
        $crawler = $this->client->request(Request::METHOD_GET, $this->urlGenerator->generate('login'));

        $form = $crawler->selectButton('btn-form')->form([
            '_username' => 'user3',
            '_password' => 'password3'
        ]);

        $this->client->submit($form);

        $this->client->followRedirect();

        $this->assertSelectorTextContains(
            'div.alert.alert-danger',
            "Le nom d'utilisateur ou le mot de passe n'existe pas"
        );
        $this->assertInputValueSame('_username', 'user3');
        $this->assertInputValueSame('_password', '');
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
    }
}

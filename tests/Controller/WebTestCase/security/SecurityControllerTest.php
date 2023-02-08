<?php

namespace tests\Controller\WebTestCase\security;

use App\Entity\User;
use App\TestsHelper\WebTestCaseHelper;
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

        $this->webTestCaseHelper->submitForm($crawler, 'btn-form', [
            '_username' => 'user0',
            '_password' => 'password0'
        ]);

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

        $this->webTestCaseHelper->submitForm($crawler, 'btn-form', [
            '_username' => 'user3',
            '_password' => 'password3'
        ]);

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

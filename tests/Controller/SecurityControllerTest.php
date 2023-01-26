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
        //Creates a KernelBrowser.
        $this->client = static::createClient();

        //Get a service router
        $this->urlGenerator = $this->client->getContainer()->get('router.default');

        //User Login
        $userRepository = $this->client->getContainer()->get('doctrine.orm.entity_manager')->getRepository(User::class);
        $this->user = $userRepository->findByEmail("test@test.fr")[0];
    }

    /**
     * check that the application redirects to the tasks page if the user is logged in
     */
    public function testLoginActionRedirectTasksUserLoggedStatusCodeOK()
    {
        //User Login
        $this->client->loginUser($this->user);

        //Move to the homepage
        $this->client->request(Request::METHOD_GET, $this->urlGenerator->generate('login'));

        //Follow redirect to login view
        $this->client->followRedirect();

        //Status code check
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
    }

    /**
     * Check the status code loginAction
     */
    public function testLoginActionUserNoLoggedStatusCodeOK()
    {
        //Move to the homepage
        $this->client->request(Request::METHOD_GET, $this->urlGenerator->generate('login'));

        //Status code check
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
    }

    /**
     * Verify that authentication successfull
     */
    public function testLoginActionAuthSuccessfull()
    {
        //Move to the login
        $crawler = $this->client->request(Request::METHOD_GET, $this->urlGenerator->generate('login'));

        //Retrieval and filling of the login form
        //Good values
        $form = $crawler->selectButton('btn-form')->form([
            '_username' => 'test',
            '_password' => 'test'
        ]);

        //Submit form
        $this->client->submit($form);

        //Follow redirect
        $this->client->followRedirect();

        $content_title =
            "Bienvenue sur Todo List, l'application vous permettant de gérer l'ensemble de vos tâches sans effort !";

        //Check if the h1 tag has the right content
        $this->assertSelectorTextContains('h1', $content_title);
    }

    /**
     * Verify that authentication failed
     */
    public function testLoginActionAuthFailed()
    {
        //Move to the login
        $crawler = $this->client->request(Request::METHOD_GET, $this->urlGenerator->generate('login'));

        //Retrieval and filling of the login form
        //Bad values
        $form = $crawler->selectButton('btn-form')->form([
            '_username' => 'test_2',
            '_password' => 'test_2'
        ]);

        //Submit form
        $this->client->submit($form);

        //Follow redirect
        $this->client->followRedirect();

        //Check that the error tag of the form appears correctly
        $this->assertSelectorTextContains('div.alert.alert-danger', 'Invalid credentials');

        //Check that the content of the username field, remembers the username
        $this->assertInputValueSame('_username', 'test_2');

        //Check that the content of the password field is empty
        $this->assertInputValueSame('_password', '');
    }

    /**
     * Verify that authentication failed with fields password empty
     */
    public function testLoginActionAuthFailedAllFieldsEmpty()
    {
        //Move to the login
        $crawler = $this->client->request(Request::METHOD_GET, $this->urlGenerator->generate('login'));

        //Retrieval and filling of the login form
        //Bad values, Password empty
        $form = $crawler->selectButton('btn-form')->form([
            '_username' => 'test_2',
            '_password' => ''
        ]);

        //Submit form
        $this->client->submit($form);

        //Follow redirect
        $this->client->followRedirect();

        //Check that the error tag of the form appears correctly
        $this->assertSelectorTextContains('div.alert.alert-danger', 'Invalid credentials');

        //Check that the content of the username field, remembers the username
        $this->assertInputValueSame('_username', 'test_2');

        //Check that the content of the password field is empty
        $this->assertInputValueSame('_password', '');
    }

    /**
     * Verify that authentication failed with fields empty
     */
    public function testLoginActionAuthFailedFieldPasswordEmpty()
    {
        //Move to the login
        $crawler = $this->client->request(Request::METHOD_GET, $this->urlGenerator->generate('login'));

        //Retrieval and filling of the login form
        //Bad values, Password empty
        $form = $crawler->selectButton('btn-form')->form([
            '_username' => '',
            '_password' => ''
        ]);

        //Submit form
        $this->client->submit($form);

        //Follow redirect
        $this->client->followRedirect();

        //Check that the error tag of the form appears correctly
        $this->assertSelectorTextContains('div.alert.alert-danger', 'Invalid credentials');

        //Check that the content of the username field, remembers the username
        $this->assertInputValueSame('_username', '');

        //Check that the content of the password field is empty
        $this->assertInputValueSame('_password', '');
    }
}

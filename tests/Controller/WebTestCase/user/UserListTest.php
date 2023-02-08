<?php

namespace tests\Controller\WebTestCase\user;

use App\Entity\User;
use App\TestsHelper\WebTestCaseHelper;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class UserListTest extends WebTestCase
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
     * verify that a non-logged-in user is redirected to login
     */
    public function testListActionUserNoLoggedRedirectLogin()
    {
        $this->webTestCaseHelper->getClientRequest('user_list');

        $this->client->followRedirect();

        $this->assertSelectorTextContains('h1', "S'identifier");
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
    }

    /**
     * Checks that a user does not have access to the user space
     */
    public function testListActionUserLoggedAccesDenied()
    {
        $this->client->loginUser($this->user);

        $this->webTestCaseHelper->getClientRequest('user_list');

        $this->getExpectedExceptionMessage('Access Denied.');
        $this->assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
    }

    /**
     * Checks that the user has access to the user part
     */
    public function testListActionAdminLogged()
    {
        $this->client->loginUser($this->admin);

        $this->webTestCaseHelper->getClientRequest('user_list');

        $this->assertSelectorTextContains('h1', "Liste des utilisateurs");
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
    }

    /**
     * test the click on the home button
     */
    public function testListActionBtnHome()
    {
        $this->client->loginUser($this->admin);

        $crawler = $this->webTestCaseHelper->getClientRequest('user_list');

        $this->webTestCaseHelper->setLinkClick($crawler, 'Accueil');

        $this->assertSelectorTextContains(
            'h1',
            "Bienvenue sur Todo List, l'application vous permettant de gérer l'ensemble de vos tâches sans effort !"
        );
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
    }

    /**
     * test the click on the user list
     */
    public function testListActionBtnUserList()
    {
        $this->client->loginUser($this->admin);

        $crawler = $this->webTestCaseHelper->getClientRequest('homepage');

        $this->webTestCaseHelper->setLinkClick($crawler, 'Gestion des utilisateurs');

        $this->assertSelectorTextContains('h1', "Liste des utilisateurs");
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
    }

    /**
     * test the click on the btn create
     */
    public function testListActionBtnUserCreate()
    {
        $this->client->loginUser($this->admin);

        $crawler = $this->webTestCaseHelper->getClientRequest('user_list');

        $this->webTestCaseHelper->setLinkClick($crawler, 'Créer un utilisateur');

        $this->assertSelectorTextContains('h1', "Créer un utilisateur");
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
    }

    /**
     * test the click on the btn edit
     */
    public function testListActionBtnUserEdit()
    {
        $this->client->loginUser($this->admin);

        $crawler = $this->webTestCaseHelper->getClientRequest('user_list');

        $link = $crawler->filter('#btn-edit-' . $this->user->getId())->link();
        $this->client->click($link);

        $this->assertSelectorTextContains('h1', "Modifier User0");
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
    }
}

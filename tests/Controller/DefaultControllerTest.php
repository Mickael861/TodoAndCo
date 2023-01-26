<?php

namespace Tests\Controller;

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
        //Move to the homepage
        $this->client->request(Request::METHOD_GET, $this->urlGenerator->generate('homepage'));

        //Follow redirect to login view
        $this->client->followRedirect();

        //Status code check
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
    }

    /**
     * Test the redirect on the login page if user is logged in
     */
    public function testIndexUserLoggedNoRedirectLoginStatusCodeOK()
    {
        //User Login
        $userRepository = $this->client->getContainer()->get('doctrine.orm.entity_manager')->getRepository(User::class);
        $user = $userRepository->findByEmail("test@test.fr");
        $this->client->loginUser($user[0]);

        //Move to the homepage
        $this->client->request(Request::METHOD_GET, $this->urlGenerator->generate('homepage'));

        //Status code check
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
    }
}

<?php

namespace App\TestsHelper;

use Exception;
use App\Entity\Task;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;

class WebTestCaseHelper
{
    /**
     * @var KernelBrowser
     */
    private $client;

    /**
     * @var object|null
     */
    private $urlGenerator;

    public function __construct(KernelBrowser $client, object $urlGenerator)
    {
        $this->client = $client;
        $this->urlGenerator = $urlGenerator;
    }

    /**
     * Make a url request to the client
     *
     * @param string $route_name Name of the route
     * @param array $parameter URL parameter
     * @return Crawler
     */
    public function getClientRequest(string $route_name, array $parameter = []): Crawler
    {
        return $this->client->request(
            Request::METHOD_GET,
            $this->urlGenerator->generate($route_name, $parameter)
        );
    }

    /**
     * Add a click on a link
     *
     * @param  Crawler $crawler Crawler
     * @param  string $text_link Textual content of the link
     * @return void
     */
    public function setLinkClick(Crawler $crawler, string $text_link): void
    {
        $link = $crawler->selectLink($text_link)->link();
        $this->client->click($link);
    }

    /**
     * submit a form
     *
     * @param  Crawler $crawler Crawler
     * @param  string $selecter Selector of the button form
     * @param  array $value_form Value of the form
     * @return void
     */
    public function submitForm(Crawler $crawler, string $selector = '', $value_form = []): void
    {
        $form = $crawler->selectButton($selector)->form($value_form);
        $this->client->submit($form);
    }

    /**
     * get entity
     *
     * @param  string $entity Entity class
     * @param  string $service Service repository
     * @param  $parameter Parameter service repository
     * @return object
     *
     */
    public function getEntity(string $entity, string $service, $parameter): object
    {
        $repository = $this->client->getContainer()->get('doctrine.orm.entity_manager')->getRepository($entity);
        $entity = $repository->{$service}($parameter);
        if (empty($entity)) {
            throw new Exception("Aucun élément trouvé");
        }

        return $entity[0];
    }

    /**
     * submit form with task identifier
     *
     * @param string $route_name Name of the route
     * @param  array $parameter_url Parameter to pass to the url
     * @param  string $title_task Title of the task
     * @param  string $service Service repository
     * @param  string $selector_btn_form The form button selector
     * @return void
     */
    public function submitFormTaskIdetifier(
        string $route_name,
        array $parameter_url,
        string $title_task,
        string $serviceEntity,
        string $selector_btn_form
    ): void {
        $crawler = $this->getClientRequest($route_name, $parameter_url);

        $id_task = $this->getEntity(Task::class, $serviceEntity, $title_task)->getId();

        $selector_completed = $selector_btn_form . '-' . $id_task;

        $this->submitForm($crawler, $selector_completed);
    }
}

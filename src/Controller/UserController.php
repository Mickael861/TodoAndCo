<?php

namespace App\Controller;

use App\Entity\User;
use App\Service\FormService;
use App\Service\UserService;
use App\Service\SecurityService;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class UserController extends AbstractController
{
    /**
     * @var ManagerRegistry
     */
    private $managerRegistry;

    /**
     * @var UserService
     */
    private $userService;

    /**
     * @var FormService
     */
    private $formService;

    /**
     * @var Security
     */
    private $security;

    public function __construct(
        ManagerRegistry $managerRegistry,
        UserService $userService,
        FormService $formService,
        Security $security
    ) {
        $this->managerRegistry = $managerRegistry;
        $this->userService = $userService;
        $this->formService = $formService;
        $this->security = $security;
    }

    /**
     * @Route("/users", name="user_list")
     */
    public function listAction(): Response
    {
        $user = $this->security->getUser();

        $this->denyAccessUnlessGranted('ROLE_ADMIN', $user);

        return $this->render('user/list.html.twig', [
            'users' => $this->managerRegistry->getRepository(User::class)->findAll()
        ]);
    }

    /**
     * @Route("/users/create", name="user_create")
     */
    public function createAction(Request $request)
    {
        $user = $this->security->getUser();

        $this->denyAccessUnlessGranted('ROLE_ADMIN', $user);

        $user = new User();

        $formUser = $this->formService->getUserForm($request, $user);
        if ($this->userService->crudUserManagement($formUser, $user)) {
            $this->addFlash('success', "L'utilisateur a bien été ajouté.");

            return $this->redirectToRoute('user_list');
        }

        return $this->render('user/create.html.twig', ['form' => $formUser->createView()]);
    }

    /**
     * @Route("/users/{id}/edit", name="user_edit")
     */
    public function editAction(User $user, Request $request)
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN', $user);

        $route_name = $request->get('_route');

        $formUser = $this->formService->getUserForm($request, $user);
        if ($this->userService->crudUserManagement($formUser, $user, $route_name)) {
            $this->addFlash('success', "L'utilisateur a bien été modifié");

            return $this->redirectToRoute('user_list');
        }

        return $this->render('user/edit.html.twig', ['form' => $formUser->createView(), 'user' => $user]);
    }
}

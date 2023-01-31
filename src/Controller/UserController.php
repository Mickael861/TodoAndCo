<?php

namespace App\Controller;

use App\Entity\User;
use App\Service\FormService;
use App\Service\UserService;
use App\Service\SecurityService;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\Request;
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
     * @var SecurityService
     */
    private $securityService;

    /**
     * @var UserService
     */
    private $userService;

    /**
     * @var FormService
     */
    private $formService;

    public function __construct(
        ManagerRegistry $managerRegistry,
        SecurityService $securityService,
        UserService $userService,
        FormService $formService
    ) {
        $this->managerRegistry = $managerRegistry;
        $this->securityService = $securityService;
        $this->userService = $userService;
        $this->formService = $formService;
    }

    /**
     * @Route("/users", name="user_list")
     */
    public function listAction(): Response
    {
        if ($this->securityService->verifyAccess()) {
            return $this->redirectToRoute('task_list');
        }

        return $this->render('user/list.html.twig', [
            'users' => $this->managerRegistry->getRepository(User::class)->findAll()
        ]);
    }

    /**
     * @Route("/users/create", name="user_create")
     */
    public function createAction(Request $request)
    {
        if ($this->securityService->verifyAccess()) {
            return $this->redirectToRoute('task_list');
        }

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
        if ($this->securityService->verifyAccess()) {
            return $this->redirectToRoute('task_list');
        }
        $route_name = $request->get('_route');
        
        $formUser = $this->formService->getUserForm($request, $user);
        if ($this->userService->crudUserManagement($formUser, $user, $route_name)) {
            $this->addFlash('success', "L'utilisateur a bien été modifié");

            return $this->redirectToRoute('user_list');
        }

        return $this->render('user/edit.html.twig', ['form' => $formUser->createView(), 'user' => $user]);
    }
}

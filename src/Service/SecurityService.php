<?php

namespace App\Service;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;

class SecurityService extends AbstractController
{
    /**
     * Verify access controle admin
     *
     * @return bool true if the user does not have the admin role, false otherwise
     */
    public function verifyAccess(): bool
    {
        $is_access_denied = false;

        if ($this->getUser()->getRoles()['ROLE'] === 'ROLE_USER') {
            $this->addFlash('error', "Vous n'avez pas le rôle néccessaire pour accéder à cette page");

            $is_access_denied = true;
        }

        return $is_access_denied;
    }

    /**
     * Checks if a user is logged in or not
     *
     * @return bool true if a user is logged in, false otherwise
     */
    public function isConnectedUser(): bool
    {
        $is_access_denied = false;

        if (!empty($this->getUser())) {
            $this->addFlash('error', "Vous êtes déjà connecté");

            $is_access_denied = true;
        }

        return $is_access_denied;
    }
}

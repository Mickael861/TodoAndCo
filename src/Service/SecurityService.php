<?php

namespace App\Service;

use App\Entity\User;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class SecurityService extends AbstractController
{
    /**
     * Verify access controle admin
     *
     * @param string|null $type Type of message
     * @param string|null $message Content of the message
     * @param string $role The role to check
     *
     * @return bool true if the user does not have the admin role, false otherwise
     */
    public function isVerifyAccess(
        string $type = null,
        string $message = null,
        string $role = 'ROLE_ADMIN'
    ): bool {
        $is_access_denied = false;

        if ($this->getUser()->getRoles()['ROLE'] === $role) {
            if ($type !== null || $message !== null) {
                $this->addFlash($type, $message);
            }

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

    /**
     * Compare the identifier of the connected user to the one indicated in parameter
     *
     * @param  int $id_compare The identifier to compare against the identifier of the logged in user
     *
     * @return bool True if the compared identifier is identical, false otherwise
     */
    public function isIdIdentifier(int $id_compare): bool
    {
        $is_identical = false;

        /**
         * @var User
         */
        $user = $this->getUser();

        if ($user->getId() === $id_compare) {
            $is_identical = true;
        }

        return $is_identical;
    }
}

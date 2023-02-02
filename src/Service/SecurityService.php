<?php

namespace App\Service;

use App\Entity\User;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class SecurityService extends AbstractController
{
    /**
     * Check a user's access against a role
     *
     * @param string|null $type Type of message
     * @param string|null $message Content of the message
     * @param string $role The role to check
     *
     * @return bool true if the user does not have the role specified, false otherwise
     */
    public function isAccessVerificationRole(
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
}

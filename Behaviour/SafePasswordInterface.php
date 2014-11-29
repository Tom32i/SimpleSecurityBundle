<?php

namespace Tom32i\Bundle\SimpleSecurityBundle\Behaviour;

use Symfony\Component\Security\Core\User\UserInterface as SymfonyUserInsterface;

/**
 * Safe password interface
 */
interface SafePasswordInterface extends SymfonyUserInsterface
{
    /**
     * Get plain password
     *
     * @return string
     */
    public function getPlainPassword();

    /**
     * Set password
     *
     * @param string $password
     *
     * @return string
     */
    public function setPassword($password);
}

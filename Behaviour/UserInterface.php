<?php

namespace Tom32i\Bundle\SimpleSecurityBundle\Behaviour;

use Serializable;
use Symfony\Component\Security\Core\User\AdvancedUserInterface;

/**
 * User interface
 */
interface UserInterface extends AdvancedUserInterface, SafePasswordInterface, ConfirmableInterface, Serializable
{
    /**
     * Get email
     *
     * @return string
     */
    public function getEmail();

    /**
     * Set email
     *
     * @param string $email
     *
     * @return UserInterface
     */
    public function setEmail($email);

    /**
     * Set enabled
     *
     * @param boolean $enabled
     *
     * @return UserInterface
     */
    public function setEnabled($enabled);
}
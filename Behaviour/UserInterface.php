<?php

namespace Tom32i\Bundle\SimpleSecurityBundle\Behaviour;

use Serializable;
use Symfony\Component\Security\Core\User\AdvancedUserInterface;

/**
 * User interface
 */
interface UserInterface extends AdvancedUserInterface, Serializable
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
    public function setEnabled($enabled = true);

    /**
     * Set plain password
     *
     * @param string $plainPassword
     */
    public function setPlainPassword($plainPassword);

    /**
     * Get plain password
     *
     * @return string
     */
    public function getPlainPassword();

    /**
     * Set encoded password
     *
     * @param string $password
     *
     * @return string
     */
    public function setPassword($password);
}

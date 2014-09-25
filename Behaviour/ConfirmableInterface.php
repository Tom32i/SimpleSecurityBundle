<?php

namespace Tom32i\Bundle\SimpleSecurityBundle\Behaviour;

/**
 * Confirmable email interface
 */
interface ConfirmableInterface
{
    /**
     * Get confirmation token
     *
     * @return string
     */
    public function getConfirmationToken();

    /**
     * Set confirmation token
     *
     * @param string $token
     *
     * @return ConfirmableInterface
     */
    public function setConfirmationToken($token = null);
}
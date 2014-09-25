<?php

namespace Tom32i\SimpleSecurityBundle\Behaviour;

/**
 * Safe password interface
 */
interface SafePasswordInterface
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
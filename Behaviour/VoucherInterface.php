<?php

namespace Tom32i\Bundle\SimpleSecurityBundle\Behaviour;

/**
 * Voucher interface
 */
interface VoucherInterface
{
    /**
     * Get user
     *
     * @return UserInterface
     */
    public function getUser();

    /**
     * Get token
     *
     * @return string
     */
    public function getToken();

    /**
     * Is expired?
     *
     * @param DateTime $date Run the test on a specific date (other current time is used).
     *
     * @return boolean
     */
    public function isExpired($date = null);
}

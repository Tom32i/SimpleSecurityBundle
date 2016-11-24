<?php

/*
 * This file is part of the Simple Security bundle.
 *
 * Copyright © Thomas Jarrand <thomas.jarrand@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tom32i\Bundle\SimpleSecurityBundle\Voucher;

use Elao\Bundle\VoucherAuthenticationBundle\Behavior\IntentedVoucherInterface;
use Elao\Bundle\VoucherAuthenticationBundle\Voucher\DisposableAuthenticationVoucher;

/**
 * Reset password Voucher
 */
class ResetPasswordVoucher extends DisposableAuthenticationVoucher implements IntentedVoucherInterface
{
    const INTENT = 'reset_password';

    /**
     * Get intent
     *
     * @return string
     */
    public function getIntent()
    {
        return static::INTENT;
    }
}
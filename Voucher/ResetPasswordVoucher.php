<?php

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

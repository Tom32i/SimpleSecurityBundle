<?php

namespace Tom32i\Bundle\SimpleSecurityBundle\Voucher;

use Elao\Bundle\VoucherAuthenticationBundle\Behavior\IntentedVoucherInterface;
use Elao\Bundle\VoucherAuthenticationBundle\Voucher\DisposableAuthenticationVoucher;

/**
 * Validate registration Voucher
 */
class ValidateRegistrationVoucher extends DisposableAuthenticationVoucher implements IntentedVoucherInterface
{
    const INTENT = 'validate_registration';

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

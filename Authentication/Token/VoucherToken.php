<?php

namespace Tom32i\Bundle\SimpleSecurityBundle\Authentication\Token;

use Symfony\Component\Security\Core\Authentication\Token\AbstractToken;

class VoucherToken extends AbstractToken
{
    /**
     * Hash
     *
     * @var string
     */
    public $hash;

    /**
     * {@inheritdoc}
     */
    public function __construct($hash, array $roles = array())
    {
        parent::__construct($roles);

        $this->hash = $hash;
    }

    /**
     * {@inheritdoc}
     */
    public function getCredentials()
    {
        return $this->hash;
    }
}

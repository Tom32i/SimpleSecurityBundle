<?php

namespace Tom32i\Bundle\SimpleSecurityBundle\Security\Authentication\Provider;

use Psr\Cache\CacheItemPoolInterface;
use Symfony\Component\Security\Core\Authentication\Provider\AuthenticationProviderInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\NonceExpiredException;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Tom32i\Bundle\SimpleSecurityBundle\Authentication\Token\VoucherToken;

class VoucherProvider implements AuthenticationProviderInterface
{
    private $userProvider;
    private $voucherProvider;

    public function __construct(UserProviderInterface $userProvider, VoucherProviderInterface $voucherProvider)
    {
        $this->userProvider = $userProvider;
        $this->voucherProvider = $voucherProvider;
    }

    public function authenticate(TokenInterface $token)
    {
        $voucher = $this->voucherProvider->getByHash($token->getCredentials());

        if (!$voucher || $voucher->isExpired()) {
            throw new AuthenticationException('No valid token found.');
        }

        $user = $this->userProvider->loadUserByUsername($voucher->getUsername());

        if (!$user) {
            throw new AuthenticationException('User not found.');
        }

        $authenticatedToken = new VoucherToken($token->getCredentials(), $user->getRoles());
        $authenticatedToken->setUser($user);

        return $authenticatedToken;
    }

    public function supports(TokenInterface $token)
    {
        return $token instanceof VoucherToken;
    }
}

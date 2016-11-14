<?php

namespace Tom32i\Bundle\SimpleSecurityBundle\Service;

use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\Encoder\EncoderFactoryInterface;
use Tom32i\Bundle\SimpleSecurityBundle\Behaviour\UserInterface;
use Tom32i\Bundle\SimpleSecurityBundle\Behaviour\SafePasswordInterface;

/**
 * Authenticator
 */
class Authenticator
{
    /**
     * Encoder Factory
     *
     * @var EncoderFactoryInterface
     */
    protected $factory;

    /**
     * The firewall to log user into
     *
     * @var string
     */
    protected $firewall;

    /**
     * Constructor
     *
     * @param EncoderFactoryInterface $factory
     */
    public function __construct(EncoderFactoryInterface $factory)
    {
        $this->factory = $factory;
    }

    /**
     * Encode User password
     *
     * @param SafePasswordInterface $user The User
     */
    public function encodePassword(SafePasswordInterface $user)
    {
        $plain = $user->getPlainPassword();

        if (!empty($plain)) {
            $encoder  = $this->factory->getEncoder($user);
            $password = $encoder->encodePassword($plain, $user->getSalt());

            $user->setPassword($password)->eraseCredentials();
        }
    }
}

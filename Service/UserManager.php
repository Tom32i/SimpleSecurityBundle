<?php

namespace Tom32i\Bundle\SimpleSecurityBundle\Service;

use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Tom32i\Bundle\SimpleSecurityBundle\Entity\User;

/**
 * User manager
 */
class UserManager
{
    /**
     * Entity Manager
     *
     * @var ObjectManager
     */
    protected $objectManager;

    /**
     * Validator
     *
     * @var Validator
     */
    protected $validator;

    /**
     * Mail manager
     *
     * @var MailManager
     */
    protected $mailer;

    /**
     * The main provider key
     *
     * @var string
     */
    protected $firewall;

    /**
     * Constructor
     *
     * @param ObjectManager $objectManager
     * @param MailManager   $mailer
     * @param string        $firewall
     */
    public function __construct(ObjectManager $objectManager, ValidatorInterface $validator, MailManager $mailer, $firewall)
    {
        $this->objectManager = $objectManager;
        $this->mailer        = $mailer;
        $this->validator     = $validator;
        $this->firewall      = $firewall;
    }

    /**
     * Create User
     *
     * @return User
     */
    public function createUser()
    {
        return new User();
    }

    /**
     * Get authentication token for a given user
     *
     * @param User $user
     *
     * @return UsernamePasswordToken
     */
    public function getAuthenticationToken(User $user)
    {
        return new UsernamePasswordToken(
            $user,
            $user->getPassword(),
            $this->firewall,
            $user->getRoles()
        );
    }

    /**
     * Register an user
     *
     * @param User $user
     */
    public function register(User $user)
    {
        $user->setEnabled(false);
        $user->setConfirmationToken();

        $errors = $this->validator->validate($user, ['Confirmation']);

        if (count($errors) > 0) {
            return $errors;
        }

        $this->objectManager->persist($user);
        $this->objectManager->flush();

        $this->mailer->sendConfirmationEmailMessage($user);

        return true;
    }

    /**
     * Validate an user's email
     *
     * @param User $user
     */
    public function validate(User $user)
    {
        $user->eraseConfirmationToken();
        $user->setEnabled(true);

        $errors = $this->validator->validate($user);

        if (count($errors) > 0) {
            return $errors;
        }

        $this->objectManager->persist($user);
        $this->objectManager->flush();

        return true;
    }
}
<?php

namespace Tom32i\Bundle\SimpleSecurityBundle\Service;

use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Tom32i\Bundle\SimpleSecurityBundle\Behaviour\UserInterface;

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
     * User class name
     *
     * @var string
     */
    protected $userClassname;

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
    public function __construct(ObjectManager $objectManager, ValidatorInterface $validator, MailManager $mailer, $userClassname, $firewall)
    {
        $this->objectManager = $objectManager;
        $this->mailer        = $mailer;
        $this->validator     = $validator;
        $this->userClassname = $userClassname;
        $this->firewall      = $firewall;
    }

    /**
     * Create User
     *
     * @return User
     */
    public function createUser()
    {
        return new $this->userClassname;
    }

    /**
     * Get User repository
     *
     * @return ObjectRepository
     */
    public function getRepository()
    {
        return $this->objectManager->getRepository($this->userClassname);
    }

    /**
     * Get authentication token for a given user
     *
     * @param UserInterface $user
     *
     * @return UsernamePasswordToken
     */
    public function getAuthenticationToken(UserInterface $user)
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
     * @param UserInterface $user
     */
    public function register(UserInterface $user)
    {
        $user->setEnabled(false);
        $user->setConfirmationToken();

        $errors = $this->validator->validate($user, null, ['Default', 'Registration', 'Confirmation']);

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
     * @param UserInterface $user
     */
    public function validate(UserInterface $user)
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
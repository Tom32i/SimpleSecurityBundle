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
     * Voucher manager
     *
     * @var VoucherManager
     */
    protected $voucherManager;

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
     * @param ValidatorInterface $validator
     * @param VoucherManager $voucherManager
     * @param MailManager $mailer
     * @param string $userClassname
     * @param string $firewall
     */
    public function __construct(ObjectManager $objectManager, ValidatorInterface $validator, VoucherManager $voucherManager, MailManager $mailer, $userClassname, $firewall)
    {
        $this->objectManager  = $objectManager;
        $this->validator      = $validator;
        $this->voucherManager = $voucherManager;
        $this->mailer         = $mailer;
        $this->userClassname  = $userClassname;
        $this->firewall       = $firewall;
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
     *
     * @return ConstraintViolationListInterface
     */
    public function register(UserInterface $user)
    {
        $user->setEnabled(false);

        $errors = $this->validator->validate($user, null, ['Default', 'Registration', 'Confirmation']);

        if (count($errors) === 0) {

            $this->objectManager->persist($user);
            $this->objectManager->flush();

            $voucher = $this->voucherManager->create($user, 'email');

            $this->objectManager->persist($voucher);
            $this->objectManager->flush();

            $this->mailer->sendConfirmationEmailMessage($user, $voucher->getToken());
        }

        return $errors;
    }

    /**
     * Validate an user's email
     *
     * @param UserInterface $user
     *
     * @return ConstraintViolationListInterface
     */
    public function validate(UserInterface $user)
    {
        $user->setEnabled(true);

        $errors = $this->validator->validate($user);

        if (count($errors) === 0) {
            $this->objectManager->persist($user);
            $this->objectManager->flush();
        }

        return $errors;
    }

    /**
     * Reset password
     *
     * @param UserInterface $user
     *
     * @return ConstraintViolationListInterface
     */
    public function resetPassword(UserInterface $user)
    {
        $errors = $this->validator->validate($user, null, ['ResetPassword']);

        if (count($errors) === 0) {
            $this->objectManager->persist($user);
            $this->objectManager->flush();

            $voucher = $this->voucherManager->create($user, 'password');

            $this->objectManager->persist($voucher);
            $this->objectManager->flush();

            $this->mailer->sendResetPasswordMessage($user, $voucher->getToken());
        }

        return $errors;
    }

    /**
     * Set password
     *
     * @param UserInterface $user
     *
     * @return ConstraintViolationListInterface
     */
    public function setPassword(UserInterface $user, $password)
    {
        $user->setPlainPassword($password);

        $errors = $this->validator->validate($user, null, ['ChangePassword']);

        if (count($errors) === 0) {
            $this->objectManager->persist($user);
            $this->objectManager->flush();
        }

        return $errors;
    }
}

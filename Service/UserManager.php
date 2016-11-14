<?php

namespace Tom32i\Bundle\SimpleSecurityBundle\Service;

use Doctrine\Common\Persistence\ObjectManager;
use Elao\Bundle\VoucherAuthenticationBundle\Behavior\VoucherProviderInterface;
use Elao\Bundle\VoucherAuthenticationBundle\Voucher\Voucher;
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
     * @var ValidatorInterface
     */
    protected $validator;

    /**
     * Voucher manager
     *
     * @var VoucherProviderInterface
     */
    protected $voucherProvider;

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
     * Constructor
     *
     * @param ObjectManager $objectManager
     * @param ValidatorInterface $validator
     * @param VoucherProviderInterface $voucherProvider
     * @param MailManager $mailer
     * @param string $userClassname
     */
    public function __construct(ObjectManager $objectManager, ValidatorInterface $validator, VoucherProviderInterface $voucherProvider, MailManager $mailer, $userClassname)
    {
        $this->objectManager = $objectManager;
        $this->validator = $validator;
        $this->voucherProvider = $voucherProvider;
        $this->mailer = $mailer;
        $this->userClassname = $userClassname;
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
            $this->objectManager->flush($user);

            $voucher = new Voucher($user->getUsername(), 'register');
            $this->voucherManager->presist($voucher);

            $this->mailer->sendRegistrationMessage($user, $voucher->getToken());
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
            $this->objectManager->flush($user);
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
            $this->objectManager->flush($user);

            $voucher = new Voucher($user->getUsername(), 'password');
            $this->voucherManager->presist($voucher);

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
            $this->objectManager->flush($user);
        }

        return $errors;
    }
}

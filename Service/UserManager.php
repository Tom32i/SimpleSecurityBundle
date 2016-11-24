<?php

namespace Tom32i\Bundle\SimpleSecurityBundle\Service;

use Doctrine\Common\Persistence\ObjectManager;
use Elao\Bundle\VoucherAuthenticationBundle\Behavior\VoucherProviderInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Tom32i\Bundle\SimpleSecurityBundle\Behaviour\UserInterface;
use Tom32i\Bundle\SimpleSecurityBundle\Voucher\ResetPasswordVoucher;
use Tom32i\Bundle\SimpleSecurityBundle\Voucher\ValidateRegistrationVoucher;

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
    //protected $userClassname;

    /**
     * Constructor
     *
     * @param ObjectManager $objectManager
     * @param ValidatorInterface $validator
     * @param UrlGeneratorInterface $router
     * @param UserPasswordEncoderInterface $passwordEncoder
     * @param VoucherProviderInterface $voucherProvider
     * @param MailManager $mailer
     * @param string $userClassname
     */
    public function __construct(
        ObjectManager $objectManager,
        ValidatorInterface $validator,
        UrlGeneratorInterface $router,
        UserPasswordEncoderInterface $passwordEncoder,
        VoucherProviderInterface $voucherProvider,
        MailManager $mailer
        //$userClassname
    ) {
        $this->objectManager = $objectManager;
        $this->validator = $validator;
        $this->router = $router;
        $this->passwordEncoder = $passwordEncoder;
        $this->voucherProvider = $voucherProvider;
        $this->mailer = $mailer;
        //$this->userClassname = $userClassname;
    }

    /**
     * Get User repository
     *
     * @return ObjectRepository
     */
    /*public function getRepository()
    {
        return $this->objectManager->getRepository($this->userClassname);
    }*/

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

        if (count($errors) > 0) {
            return $errors;
        }

        $password = $this->passwordEncoder->encodePassword($user, $user->getPlainPassword());

        $user->setPassword($password);

        $this->objectManager->persist($user);
        $this->objectManager->flush($user);

        $voucher = new ValidateRegistrationVoucher($user->getUsername());

        $this->voucherProvider->persist($voucher);

        $url = $this->router->generate(
            'voucher',
            ['token' => $voucher->getToken()],
            UrlGeneratorInterface::ABSOLUTE_URL
        );

        $this->mailer->sendRegistrationMessage($user, $url);

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

        if (count($errors) > 0) {
            return $errors;
        }

        $this->objectManager->persist($user);
        $this->objectManager->flush($user);

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
        $voucher = new ResetPasswordVoucher($user->getUsername());

        $this->voucherProvider->persist($voucher);

        $url = $this->router->generate(
            'voucher',
            ['token' => $voucher->getToken()],
            UrlGeneratorInterface::ABSOLUTE_URL
        );

        $this->mailer->sendResetPasswordMessage($user, $url);
    }

    /**
     * Set password
     *
     * @param UserInterface $user
     *
     * @return ConstraintViolationListInterface
     */
    public function setPassword(UserInterface $user)
    {
        $errors = $this->validator->validate($user, null, ['ChangePassword']);

        if (count($errors) === 0) {
            $password = $this->passwordEncoder->encodePassword($user, $user->getPlainPassword());

            $user->setPassword($password);

            $this->objectManager->persist($user);
            $this->objectManager->flush($user);
        }

        return $errors;
    }
}

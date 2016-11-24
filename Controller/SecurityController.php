<?php

/*
 * This file is part of the Simple Security bundle.
 *
 * Copyright © Thomas Jarrand <thomas.jarrand@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tom32i\Bundle\SimpleSecurityBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authorization\Voter\AuthenticatedVoter;
use Tom32i\Bundle\SimpleSecurityBundle\Form\Type\ForgotPasswordType;
use Tom32i\Bundle\SimpleSecurityBundle\Form\Type\LoginType;
use Tom32i\Bundle\SimpleSecurityBundle\Form\Type\RegisterType;
use Tom32i\Bundle\SimpleSecurityBundle\Form\Type\UserPasswordType;
use Tom32i\Bundle\SimpleSecurityBundle\Voucher\ResetPasswordVoucher;
use Tom32i\Bundle\SimpleSecurityBundle\Voucher\ValidateRegistrationVoucher;

/**
 * Security Controller
 */
class SecurityController extends Controller
{
    /**
     * Simple login action
     *
     * @return Response
     */
    public function loginAction()
    {
        if ($this->isLoggedIn()) {
            return $this->redirectOnSuccess();
        }

        $authenticationUtils = $this->get('security.authentication_utils');

        $form = $this->createForm(
            LoginType::class,
            [
                'username' => $authenticationUtils->getLastUsername(),
                'password' => null,
                'remember_me' => true,
            ]
        );

        if ($error = $authenticationUtils->getLastAuthenticationError()) {
            $form->addError(
                new FormError(
                    $error->getMessage(),
                    $error->getMessageKey(),
                    $error->getMessageData(),
                    null,
                    $error
                )
            );
        }

        return $this->render(
            'Tom32iSimpleSecurityBundle:Security:login.html.twig',
            ['form' => $form->createView()]
        );
    }

    /**
     * Simple register action
     *
     * @return Response
     */
    public function registerAction(Request $request)
    {
        if ($this->isLoggedIn()) {
            return $this->redirectOnSuccess();
        }

        $form = $this->createForm(
            RegisterType::class,
            null,
            ['data_class' => $this->getParameter('tom32i_simple_security.parameters.user_class')]
        );

        $form->handleRequest($request);

        if ($form->isValid()) {
            $errors = $this->getUserManager()->register($form->getData());

            if (count($errors) === 0) {
                return $this->render('Tom32iSimpleSecurityBundle:Security:registered.html.twig');
            }

            foreach ($errors as $error) {
                $form->addError(
                    new FormError($error->getMessage(), 'form.register.error', ['message' => $error->getMessage()])
                );
            }
        }

        return $this->render(
            'Tom32iSimpleSecurityBundle:Security:register.html.twig',
            ['form' => $form->createView()]
        );
    }

    /**
     * Validate the registration of the user
     *
     * @return Response
     */
    public function validateRegistrationAction()
    {
        $this->denyAccessUnlessGranted('voucher', ValidateRegistrationVoucher::INTENT);

        $errors = $this->getUserManager()->validate($this->getUser());

        if (count($errors) === 0) {
            return $this->redirectOnSuccess();
        }

        return $this->render(
            'Tom32iSimpleSecurityBundle:Security:validate_registration.html.twig',
            ['errors' => $errors]
        );
    }

    /**
     * Forgot password
     *
     * @return Response
     */
    public function forgotPasswordAction(Request $request)
    {
        if ($this->isLoggedIn()) {
            return $this->redirectOnSuccess();
        }

        $form = $this->createForm(ForgotPasswordType::class);

        $form->handleRequest($request);

        if ($form->isValid()) {
            $email = $form->getData()['email'];
            $classname = $this->getParameter('tom32i_simple_security.parameters.user_class');
            $user = $this->getDoctrine()->getRepository($classname)->loadUserByUsername($email);

            if ($user) {
                $this->getUserManager()->resetPassword($user);
            }

            return $this->render('Tom32iSimpleSecurityBundle:Security:password_confirmation.html.twig', ['email' => $email]);
        }

        return $this->render(
            'Tom32iSimpleSecurityBundle:Security:forgot_password.html.twig',
            ['form' => $form->createView()]
        );
    }

    /**
     * Reset password action
     *
     * @param Request $request
     */
    public function resetPasswordAction(Request $request)
    {
        $this->denyAccessUnlessGranted('voucher', ResetPasswordVoucher::INTENT);

        $user = $this->getUser();
        $form = $this->createForm(UserPasswordType::class, $user, [
            'current_password' => false,
        ]);

        $form->handleRequest($request);

        if ($form->isValid()) {
            $errors = $this->getUserManager()->setPassword($user);

            if (count($errors) === 0) {
                return $this->redirectOnSuccess();
            }
        }

        return $this->render(
            'Tom32iSimpleSecurityBundle:Security:reset_password.html.twig',
            ['form' => $form->createView()]
        );
    }

    /**
     * Is logged in
     *
     * @return bool
     */
    protected function isLoggedIn()
    {
        return $this->isGranted(AuthenticatedVoter::IS_AUTHENTICATED_REMEMBERED);
    }

    /**
     * Redirect user on login success
     *
     * @return RedirectResponse
     */
    protected function redirectOnSuccess()
    {
        $route = $this->getSuccessRoute();

        return $this->redirectToRoute($route['name'], $route['parameters'] ?: []);
    }

    /**
     * Get success route
     *
     * @return strng
     */
    protected function getSuccessRoute()
    {
        return $this->container->getParameter('tom32i_simple_security.parameters.redirect_after_authentication');
    }

    /**
     * Get user manager
     *
     * @return UserManager
     */
    protected function getUserManager()
    {
        return $this->get('tom32i_simple_security.manager.user');
    }
}

<?php

namespace Tom32i\Bundle\SimpleSecurityBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\FormError;
use Tom32i\Bundle\SimpleSecurityBundle\Form\Type\LoginType;
use Tom32i\Bundle\SimpleSecurityBundle\Form\Type\RegisterType;

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
                return $this->render('Tom32iSimpleSecurityBundle:Security:confirmation.html.twig');
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
     * Is logged in
     *
     * @return boolean
     */
    protected function isLoggedIn()
    {
        return $this
            ->get('security.authorization_checker')
            ->isGranted('IS_AUTHENTICATED_REMEMBERED');
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

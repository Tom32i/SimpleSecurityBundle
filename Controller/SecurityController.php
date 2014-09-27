<?php

namespace Tom32i\Bundle\SimpleSecurityBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Security\Core\SecurityContext;
use Symfony\Component\Form\FormError;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

/**
 * Security Controller
 */
class SecurityController extends Controller
{
    /**
     * @Route("/login", name="login")
     * @Template()
     */
    public function loginAction(Request $request)
    {
        if ($this->isLoggedIn()) { return $this->redirectOnSuccess(); }

        $session = $request->getSession();
        $user    = ['_username' => $session->get(SecurityContext::LAST_USERNAME), '_password' => null, '_remeber_me' => true];
        $form    = $this->createForm('security_login', $user, ['action' => $this->generateUrl('login_check')]);

        if ($request->attributes->has(SecurityContext::AUTHENTICATION_ERROR)) {
            $error = $request->attributes->get(SecurityContext::AUTHENTICATION_ERROR);
        } else {
            $error = $session->get(SecurityContext::AUTHENTICATION_ERROR);
            $session->remove(SecurityContext::AUTHENTICATION_ERROR);
        }

        if ($error) {
            if ($error instanceof LockedException) {
                $code = 'login.error.locked';
            } elseif ($error instanceof DisabledException) {
                $code = 'login.error.disabled';
            } else {
                $code = 'login.error.failed';
            }

            $form->addError(
                new FormError($error->getMessage(), $code, ['message' => $error->getMessage()])
            );
        }

        return ['form' => $form->createView()];
    }

    /**
     * @Route("/register", name="register")
     * @Template()
     */
    public function registerAction(Request $request)
    {
        if ($this->isLoggedIn()) { return $this->redirectOnSuccess(); }

        $user = $this->getUserManager()->createUser();
        $form = $this->createForm('security_register', $user, ['action' => $this->generateUrl('register')]);

        if ($request->isMethod('POST')) {

            $form->handleRequest($request);

            if ($form->isValid()) {

                $result = $this->getUserManager()->register($user);

                if ($result === true) {
                    return $this->redirect($this->generateUrl('email_confirmation'));
                }

                foreach ($result as $error) {
                    $form->addError(
                        new FormError($error->getMessage(), 'form.register.error', ['message' => $error->getMessage()])
                    );
                }
            }
        }

        return ['form' => $form->createView()];
    }

    /**
     * @Route("/register/email", name="email_confirmation")
     * @Template("Tom32iSimpleSecurityBundle:Security:email_confirmation.html.twig")
     */
    public function emailConfirmationAction()
    {
        if ($this->isLoggedIn()) { return $this->redirectOnSuccess(); }
    }

    /**
     * @Route("/register/email/{token}", name="email_validation")
     * @Template()
     */
    public function emailValidationAction($token)
    {
        if ($this->isLoggedIn()) { return $this->redirectOnSuccess(); }

        $user = $this->getUserManager()->getRepository()->findOneBy(['confirmationToken' => $token, 'enabled' => false]);

        if (!$user) {
            throw $this->createNotFoundException('This token has expired.');
        }

        $result = $this->getUserManager()->validate($user);

        if ($result === true) {

            $this->logUserIn($user);

            return $this->redirectOnSuccess();
        }

        return ['errors' => $result];
    }

    /**
     * Is logged in
     *
     * @return boolean
     */
    protected function isLoggedIn()
    {
        return $this->getSecurityContext()->isGranted('IS_AUTHENTICATED_REMEMBERED');
    }

    /**
     * Redirect user on login success
     *
     * @return RedirectResponse
     */
    protected function redirectOnSuccess()
    {
        return $this->redirect($this->generateUrl($this->getRedirectRoute()));
    }

    /**
     * Log the user in
     *
     * @param User $user The user to impersonate
     */
    protected function logUserIn(User $user)
    {
        $token = $this->getUserManager()->getAuthenticationToken($user);

        $this->getSecurityContext()->setToken($token);
    }

    /**
     * Get security context
     *
     * @return SecurityContextInterface
     */
    protected function getSecurityContext()
    {
        return $this->get('security.context');
    }

    /**
     * Get user manager
     *
     * @return UserManager
     */
    protected function getUserManager()
    {
        return $this->get('tom32i.simple_security.manager.user');
    }

    /**
     * Get user manager
     *
     * @return UserManager
     */
    protected function getRedirectRoute()
    {
        return $this->getParameter('tom32i_simple_security.login_success_redirect');
    }
}

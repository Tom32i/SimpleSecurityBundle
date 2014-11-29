<?php

namespace Tom32i\Bundle\SimpleSecurityBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\SecurityContext;
use Symfony\Component\Form\FormError;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

/**
 * Login Controller
 */
class LoginController extends BaseController
{
    /**
     * @Route("/login", name="login")
     * @Template
     */
    public function loginAction(Request $request)
    {
        if ($this->isLoggedIn()) { return $this->redirectOnSuccess(); }

        $session = $request->getSession();

        $user = [
            '_username' => $session->get(SecurityContext::LAST_USERNAME),
            '_password' => null,
            '_remeber_me' => true
        ];

        $form = $this->createForm('login', $user, [
            'action' => $this->generateUrl('login_check')
        ]);

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
}

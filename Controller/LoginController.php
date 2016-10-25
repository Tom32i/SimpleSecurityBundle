<?php

namespace Tom32i\Bundle\SimpleSecurityBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\SecurityContext;
use Tom32i\Bundle\SimpleSecurityBundle\Form\Type\LoginType;

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
        if ($this->isLoggedIn()) {
            return $this->redirectOnSuccess();
        }

        $authenticationUtils = $this->get('security.authentication_utils');

        $form = $this->createForm(LoginType::class, [
            'username'    => $authenticationUtils->getLastUsername(),
            'password'    => null,
            'remember_me' => true,
        ]);

        if ($error = $authenticationUtils->getLastAuthenticationError()) {
            $form->addError(new FormError(
                $error->getMessage(),
                $error->getMessageKey(),
                $error->getMessageData(),
                null,
                $error
            ));
        }

        return ['form' => $form->createView()];
    }
}

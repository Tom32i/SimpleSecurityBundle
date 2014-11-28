<?php

namespace Tom32i\Bundle\SimpleSecurityBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Tom32i\Bundle\SimpleSecurityBundle\Behaviour\UserInterface;

/**
 * Security Controller
 */
abstract class BaseController extends Controller
{
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
        $route = $this->container->getParameter('tom32i_simple_security.parameters.redirect_after_authentication');

        return $this->redirect($this->generateUrl($route['name'], $route['parameters'] ?: []));
    }

    /**
     * Log the user in
     *
     * @param User $user The user to impersonate
     */
    protected function logUserIn(UserInterface $user)
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
        return $this->get('tom32i_simple_security.manager.user');
    }
    /**
     * Get voucher manager
     *
     * @return VoucherManager
     */
    protected function getVoucherManager()
    {
        return $this->get('tom32i_simple_security.manager.voucher');
    }
}

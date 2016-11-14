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
        return $this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_REMEMBERED');
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
     * Log the user in
     *
     * @param User $user The user to impersonate
     */
    /*protected function logUserIn(UserInterface $user)
    {
        $token = $this->getAuthenticator()->getAuthenticationToken($user);

        $this->getTokenStorage()->setToken($token);
    }*/

    /**
     * Get token storage
     *
     * @return TokenStorage
     */
    /*protected function getTokenStorage()
    {
        return $this->get('security.token_storage');
    }*/

    /**
     * Get authenticator
     *
     * @return Authenticator
     */
    /*protected function getAuthenticator()
    {
        return $this->get('tom32i_simple_security.authenticator');
    }*/
}

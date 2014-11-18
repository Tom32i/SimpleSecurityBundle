<?php

namespace Tom32i\Bundle\SimpleSecurityBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Security\Core\SecurityContext;
use Symfony\Component\Form\FormError;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Tom32i\Bundle\SimpleSecurityBundle\Behaviour\UserInterface;
use Tom32i\Bundle\SimpleSecurityBundle\Entity\Voucher;

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
        $form    = $this->createForm('login', $user, ['action' => $this->generateUrl('login_check')]);

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
        $form = $this->createForm('register', $user, ['action' => $this->generateUrl('register')]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $result = $this->getUserManager()->register($user);

            if (count($result) === 0) {
                return $this->render('Tom32iSimpleSecurityBundle:Security:email_confirmation.html.twig');
            }

            foreach ($result as $error) {
                $form->addError(
                    new FormError($error->getMessage(), 'form.register.error', ['message' => $error->getMessage()])
                );
            }
        }

        return ['form' => $form->createView()];
    }

    /**
     * @Route("/register/email/{token}", name="email_validation", defaults={"type"="email"})
     * @ParamConverter(options={"repository_method"="findNonExpired"})
     * @Template()
     */
    public function emailValidationAction(Voucher $voucher)
    {
        if ($this->isLoggedIn()) { return $this->redirectOnSuccess(); }

        $user   = $this->getVoucherManager()->activate($voucher);
        $errors = $this->getUserManager()->validate($user);

        if (count($errors) === 0) {
            $this->logUserIn($user);

            return $this->redirectOnSuccess();
        }

        return ['errors' => $errors];
    }

    /**
     * @Route("/forgot-password", name="forgot_password")
     * @Template("Tom32iSimpleSecurityBundle:Security:forgot_password.html.twig")
     */
    public function forgotPasswordAction(Request $request)
    {
        if ($this->isLoggedIn()) { return $this->redirectOnSuccess(); }

        $form = $this->createForm('forgot_password', [], ['action' => $this->generateUrl('forgot_password')]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $user = $this->getUserManager()->getRepository()->findOneBy([
                'email' => $form->getData(),
            ]);

            if ($user) {
                $this->getUserManager()->resetPassword($user);
            }

            return $this->render('Tom32iSimpleSecurityBundle:Security:forgot_password_confirmation.html.twig');
        }

        return ['form' => $form->createView()];
    }

    /**
     * @Route("/forgot-password/{token}", name="choose_password", defaults={"type"="password"})
     * @ParamConverter(options={"repository_method"="findNonExpired"})
     * @Template("Tom32iSimpleSecurityBundle:Security:choose_password.html.twig")
     */
    public function choosePasswordAction(Request $request, Voucher $voucher)
    {
        if ($this->isLoggedIn()) { return $this->redirectOnSuccess(); }

        $user = $voucher->getUser();
        $form = $this->createForm('user_password', $user, [
            'action'           => $this->generateUrl('choose_password', ['token' => $voucher->getToken()]),
            'current_password' => false,
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $errors = $this->getUserManager()->setPassword($user, $user->getPlainPassword());

            if (count($errors) === 0) {
                $this->logUserIn($user);
                $this->getVoucherManager()->activate($voucher);

                return $this->redirectOnSuccess();
            }
        }

        return ['form' => $form->createView()];
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
        return $this->get('tom32i.simple_security.manager.user');
    }
    /**
     * Get voucher manager
     *
     * @return VoucherManager
     */
    protected function getVoucherManager()
    {
        return $this->get('tom32i.simple_security.manager.voucher');
    }

    /**
     * Get user manager
     *
     * @return UserManager
     */
    protected function getRedirectRoute()
    {
        return $this->container->getParameter('tom32i_simple_security.login_success_redirect');
    }
}

<?php

namespace Tom32i\SimpleSecurityBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\SecurityContext;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Security\Core\SecurityContextInterface;
use Symfony\Component\Form\FormError;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Tom32i\SimpleSecurityBundle\Service\UserManager;
use Tom32i\SimpleSecurityBundle\Form\Type\LoginType;
use Tom32i\SimpleSecurityBundle\Form\Type\RegisterType;
use Tom32i\SimpleSecurityBundle\Entity\User;

/**
 * Security Controller
 */
class SecurityController extends Controller
{
    /**
     * Security context
     *
     * @var SecurityContextInterface
     */
    protected $securityContext;

    /**
     * User manager
     *
     * @var UserManager
     */
    protected $userManager;

    /**
     * Login success redirect route
     *
     * @var string
     */
    protected $redirectRoute;

    /**
     * Constructor
     *
     * @param SecurityContextInterface $securityContext
     * @param UserManager $userManager
     * @param string $redirectRoute
     */
    public function __construct(SecurityContextInterface $securityContext, UserManager $userManager, $redirectRoute)
    {
        $this->securityContext = $securityContext;
        $this->userManager     = $userManager;
        $this->redirectRoute   = $redirectRoute;
    }

    /**
     * @Route("/login", name="login")
     * @Template()
     */
    public function loginAction(Request $request)
    {
        if ($this->isLoggedIn()) { return $this->redirectOnSuccess(); }

        $session = $request->getSession();

		$form = $this->createForm(
            new LoginType,
            [
                '_username'   => $session->get(SecurityContext::LAST_USERNAME),
                '_password'   => null,
                '_remeber_me' => true,
            ],
            [
                'action' => $this->generateUrl('login_check'),
                'method' => 'POST',
                'submit' => true,
            ]
        );

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

        $user = $this->userManager->createUser();
        $form = $this->createForm(
            new RegisterType,
            $user,
            [
                'action' => $this->generateUrl('register'),
                'method' => 'POST',
                'submit' => true,
            ]
        );

        if ($request->isMethod('POST')) {

            $form->handleRequest($request);

            if ($form->isValid()) {

                $result = $this->userManager->register($user);

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

        $user = $this
            ->getDoctrine()
            ->getManager()
            ->getRepository('Tom32iSimpleSecurityBundle:User')
            ->findOneBy(['confirmationToken' => $token, 'enabled' => false]);

        if (!$user) {
            throw $this->createNotFoundException('This token has expired.');
        }

        $result = $this->userManager->validate($user);

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
        return $this->get('security.context')->isGranted('ROLE_USER');
    }

    /**
     * Redirect user on login success
     *
     * @return RedirectResponse
     */
    protected function redirectOnSuccess()
    {
        return $this->redirect($this->generateUrl($this->redirectRoute));
    }

    /**
     * Log the user in
     *
     * @param User $user The user to impersonate
     */
    protected function logUserIn(User $user)
    {
        $token = $this->userManager->getAuthenticationToken($user);

        $this->get('security.context')->setToken($token);
    }
}

<?php

namespace Tom32i\Bundle\SimpleSecurityBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Tom32i\Bundle\SimpleSecurityBundle\Entity\Voucher;

/**
 * Password Controller
 */
class PasswordController extends BaseController
{
    /**
     * @Route("/forgot-password", name="forgot_password")
     * @Template
     */
    public function forgotAction(Request $request)
    {
        if ($this->isLoggedIn()) {
            return $this->redirectOnSuccess();
        }

        $form = $this->createForm('forgot_password', [], ['action' => $this->generateUrl('forgot_password')]);

        if ($form->handleRequest($request)->isSubmitted() && $form->isValid()) {
            $user = $this->getUserManager()->getRepository()->findOneBy([
                'email' => $form->getData(),
            ]);

            if ($user) {
                $this->getUserManager()->resetPassword($user);
            }

            return $this->render('Tom32iSimpleSecurityBundle:Password:confirmation.html.twig');
        }

        return ['form' => $form->createView()];
    }

    /**
     * @Route("/forgot-password/{token}", name="choose_password", defaults={"type"="password"})
     * @ParamConverter(options={"repository_method"="findNonExpiredByToken"})
     * @Template
     */
    public function chooseAction(Request $request, Voucher $voucher)
    {
        if ($this->isLoggedIn()) {
            return $this->redirectOnSuccess();
        }

        $user = $voucher->getUser();
        $form = $this->createForm('user_password', $user, [
            #'action' => $this->generateUrl('choose_password', ['token' => $voucher->getToken()]),
            'current_password' => false,
        ]);

        $form->handleRequest($request);

        if ($form->isValid()) {
            $errors = $this->getUserManager()->setPassword($user, $user->getPlainPassword());

            if (count($errors) === 0) {
                $this->logUserIn($user);
                $this->getVoucherManager()->activate($voucher);
                $this->getVoucherManager()->clearTrash();

                return $this->redirectOnSuccess();
            }
        }

        return ['form' => $form->createView()];
    }
}

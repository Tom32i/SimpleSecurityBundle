<?php

namespace Tom32i\Bundle\SimpleSecurityBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Form\FormError;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Tom32i\Bundle\SimpleSecurityBundle\Entity\Voucher;

/**
 * Register Controller
 */
class RegisterController extends BaseController
{
    /**
     * @Route("/register", name="register")
     * @Template
     */
    public function registerAction(Request $request)
    {
        if ($this->isLoggedIn()) { return $this->redirectOnSuccess(); }

        $user = $this->getUserManager()->createUser();
        $form = $this->createForm('register', $user, ['action' => $this->generateUrl('register')]);

        if ($form->handleRequest($request)->isSubmitted() && $form->isValid()) {

            $result = $this->getUserManager()->register($user);

            if (count($result) === 0) {
                return $this->render('Tom32iSimpleSecurityBundle:Register:confirmation.html.twig');
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
     * @Route("/register/{token}", name="register_validation", defaults={"type"="registration"})
     * @ParamConverter(options={"repository_method"="findNonExpiredByToken"})
     * @Template
     */
    public function validationAction(Voucher $voucher)
    {
        if ($this->isLoggedIn()) { return $this->redirectOnSuccess(); }

        $user   = $this->getVoucherManager()->activate($voucher);
        $errors = $this->getUserManager()->validate($user);

        if (count($errors) === 0) {
            $this->logUserIn($user);
            $this->getVoucherManager()->clearTrash();

            return $this->redirectOnSuccess();
        }

        return ['errors' => $errors];
    }
}

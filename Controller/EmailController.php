<?php

namespace Tom32i\Bundle\SimpleSecurityBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Tom32i\Bundle\SimpleSecurityBundle\Entity\Voucher;

/**
 * Email Controller
 */
class EmailController extends BaseController
{
    /**
     * @Route("/email-validation/{token}", name="email_validate", defaults={"type"="email"})
     * @ParamConverter("voucher", class="Tom32iSimpleSecurityBundle:Voucher", options={"repository_method"="findNonExpiredByToken"})
     * @Template("Tom32iSimpleSecurityBundle:Email:validate.html.twig")
     */
    public function validateEmailAction(Voucher $voucher)
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
}

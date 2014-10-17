<?php

namespace Tom32i\Bundle\SimpleSecurityBundle\Service;

use Swift_Mailer;
use Swift_Message;
use Swift_SwiftException;
use Symfony\Component\Templating\EngineInterface;
use Symfony\Component\Translation\TranslatorInterface;
use Symfony\Component\Routing\RouterInterface;
use Tom32i\Bundle\SimpleSecurityBundle\Behaviour\ConfirmableInterface;

/**
 * Mail Manager Class
 */
class MailManager
{
    /**
     * The SwiftMailer service
     *
     * @var Swift_Mailer
     */
    protected $mailer;

    /**
     * The Translator service
     *
     * @var TranslatorInterface
     */
    protected $translator;

    /**
     * The Twig templating service
     *
     * @var EngineInterface
     */
    protected $templating;

    /**
     * Email address for from field
     *
     * @var string
     */
    protected $from;

    /**
     * Root url
     *
     * @var string
     */
    protected $root;

    public function __construct(Swift_Mailer $mailer, TranslatorInterface $translator, EngineInterface $templating, RouterInterface $router, $from = "")
    {
        $context = $router->getContext();
        $root    = $context->getScheme() . '://' . $context->getHost();

        $this->mailer     = $mailer;
        $this->translator = $translator;
        $this->templating = $templating;
        $this->from       = $from;
        $this->root       = $root;
    }

    /**
     * Send an email to the user to confirm its email address
     *
     * @param ConfirmableInterface $user The user to send the email to.
     */
    protected function createMessage($title, $to, $template, $parameters = array(), $from = null, $type = 'text/html')
    {
        if ($from === null) {
            $from = $this->from;
        }

        return Swift_Message::newInstance()
            ->setSubject($this->translator->trans($title))
            ->setFrom($this->from)
            ->setTo($to)
            ->setBody($this->templating->render($template, $parameters), 'text/html');
    }

    /**
     * Send an email to the user to confirm its email address
     *
     * @param ConfirmableInterface $user The user to send the email to.
     */
    public function sendConfirmationEmailMessage(ConfirmableInterface $user)
    {
        $message = $this->createMessage(
            'email.confirmation.title',
            array($user->getEmail() => $user->getUsername()),
            '@Tom32iSimpleSecurity/Email/validation.html.twig',
            array(
                'name'  => $user->getUsername(),
                'token' => $user->getConfirmationToken(),
                'root'  => $this->root,
            )
        );

        $this->mailer->send($message);
    }

    /**
     * Send an email to the user to confirm its email address
     *
     * @param ConfirmableInterface $user The user to send the email to.
     */
    public function sendNewPasswordMessage(ConfirmableInterface $user)
    {
        $message = $this->createMessage(
            'email.reset_password.title',
            array($user->getEmail() => $user->getUsername()),
            '@Tom32iSimpleSecurity/Email/reset_password.html.twig',
            array(
                'name'  => $user->getUsername(),
                'token' => $user->getConfirmationToken(),
                'root'  => $this->root,
            )
        );

        $this->mailer->send($message);
    }
}
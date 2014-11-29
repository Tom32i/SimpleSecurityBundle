<?php

namespace Tom32i\Bundle\SimpleSecurityBundle\Service;

use Swift_Mailer;
use Swift_Message;
use Swift_SwiftException;
use Symfony\Component\Templating\EngineInterface;
use Symfony\Component\Translation\TranslatorInterface;
use Symfony\Component\Routing\RouterInterface;
use Tom32i\Bundle\SimpleSecurityBundle\Behaviour\UserInterface;

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
     * Create a new message
     *
     * @param string $title
     * @param strin|array $to
     * @param string $template
     * @param array $parameters
     * @param string|array $from
     * @param string $type
     *
     * @return Swift_Message
     */
    protected function createMessage($title, $to, $template, $parameters = [], $from = null, $type = 'text/html')
    {
        if ($from === null) {
            $from = $this->from;
        }

        return Swift_Message::newInstance()
            ->setSubject($this->translator->trans($title, [], 'email'))
            ->setFrom($this->from)
            ->setTo($to)
            ->setBody($this->templating->render($template, $parameters), 'text/html');
    }

    /**
     * Send an email to the user to confirm its email address after registration
     *
     * @param UserInterface $user The user to send the email to
     * @param string $token The token
     */
    public function sendRegistrationMessage(UserInterface $user, $token)
    {
        $message = $this->createMessage(
            'registration.title',
            [$user->getEmail() => $user->getUsername()],
            '@Tom32iSimpleSecurity/Message/registration.html.twig',
            [
                'name'  => $user->getUsername(),
                'token' => $token,
                'root'  => $this->root,
            ]
        );

        $this->mailer->send($message);
    }

    /**
     * Send an email to the user to confirm its email address
     *
     * @param UserInterface $user The user to send the email to
     * @param string $token The token
     */
    public function sendConfirmationEmailMessage(UserInterface $user, $token)
    {
        $message = $this->createMessage(
            'confirmation.title',
            [$user->getEmail() => $user->getUsername()],
            '@Tom32iSimpleSecurity/Message/validation.html.twig',
            [
                'name'  => $user->getUsername(),
                'token' => $token,
                'root'  => $this->root,
            ]
        );

        $this->mailer->send($message);
    }

    /**
     * Send an email to the user to choose a new password
     *
     * @param UserInterface $user The user to send the email to
     * @param string $token The token
     */
    public function sendResetPasswordMessage(UserInterface $user, $token)
    {
        $message = $this->createMessage(
            'reset_password.title',
            [$user->getEmail() => $user->getUsername()],
            '@Tom32iSimpleSecurity/Message/reset_password.html.twig',
            [
                'name'  => $user->getUsername(),
                'token' => $token,
                'root'  => $this->root,
            ]
        );

        $this->mailer->send($message);
    }
}

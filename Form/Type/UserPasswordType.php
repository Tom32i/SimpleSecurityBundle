<?php

namespace Tom32i\Bundle\SimpleSecurityBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Security\Core\Validator\Constraints\UserPassword;

/**
 * User Password type
 */
class UserPasswordType extends AbstractType
{
    /**
     * User class name
     *
     * @var string
     */
    protected $userClassname;

    /**
     * Constructor
     *
     * @param string $userClassname
     */
    public function __construct($userClassname)
    {
        $this->userClassname = $userClassname;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        if ($options['current_password']) {
            $builder->add(
                'password',
                'password',
                ['constraints' => [new UserPassword]]
            );
        }

        $builder
            ->add(
                'plainPassword',
                'repeated',
                [
                    'type'            => 'password',
                    'invalid_message' => 'user.password.mismatch',
                ]
            );
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            [
                'data_class'         => $this->userClassname,
                'validation_groups'  => ['ChangePassword'],
                'method'             => 'POST',
                'cascade_validation' => true,
                'submit'             => true,
                'current_password'   => true,
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'user_password';
    }
}

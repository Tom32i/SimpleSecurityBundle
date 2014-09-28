<?php

namespace Tom32i\Bundle\SimpleSecurityBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

/**
 * Register type
 */
class RegisterType extends AbstractType
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
     * @param string $class
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
        $builder
            ->add('name', 'text')
            ->add('email', 'email')
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
                'validation_groups'  => ['Default', 'Registration'],
                'cascade_validation' => true,
                'method'             => 'POST',
                'submit'             => true,
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'register';
    }
}
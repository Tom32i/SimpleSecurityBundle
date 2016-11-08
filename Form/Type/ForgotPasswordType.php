<?php

namespace Tom32i\Bundle\SimpleSecurityBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Bridge\Doctrine\Form\Type;

/**
 * Forgot Password type
 */
class ForgotPasswordType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('email', Type\TextType::class)
            ->add('submit', Type\SubmitType::class)
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefault('method', 'POST');
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'forgot_password';
    }
}

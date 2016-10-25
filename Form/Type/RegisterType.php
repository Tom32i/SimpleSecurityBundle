<?php

namespace Tom32i\Bundle\SimpleSecurityBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

/**
 * Register type
 */
class RegisterType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('username', Type\TextType::class)
            ->add('email', Type\EmailType::class)
            ->add('plainPassword', Type\RepeatedType::class, [
                'type' => Type\PasswordType::class,
                /*'first_options'  => [
                    'label' => 'Mot de passe',
                ],
                'second_options' => [
                    'label' => 'Confirmation de mot de passe',
                ],
                'invalid_message' => 'user.password.mismatch',*/
            ])
            ->add('submit', Type\SubmitType::class)
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults([
            //'data_class'         => $this->userClassname,
            //'cascade_validation' => true,
            'method'            => 'POST',
            'validation_groups' => ['Default', 'Registration'],
            'empty_data'        => function (Options $options) {
                return new $options['data_class']();
            },
        ]);
    }
}

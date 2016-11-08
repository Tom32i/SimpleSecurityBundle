<?php

namespace Tom32i\Bundle\SimpleSecurityBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Validator\Constraints\UserPassword;

/**
 * User Email type
 */
class UserEmailType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('email', Type\EmailType::class);

        if ($options['current_password']) {
            $builder->add('password', Type\PasswordType::class, ['constraints' => [new UserPassword()]]);
        }

        $builder->add('submit', Type\SubmitType::class);
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'method' => 'POST',
            'validation_groups' => ['ChangePassword'],
            #'cascade_validation' => true,
            'current_password' => true,
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'user_email';
    }
}

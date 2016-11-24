<?php

/*
 * This file is part of the Simple Security bundle.
 *
 * Copyright © Thomas Jarrand <thomas.jarrand@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tom32i\Bundle\SimpleSecurityBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Login type
 */
class LoginType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('username', Type\TextType::class)
            ->add('password', Type\PasswordType::class)
            ->add('remember_me', Type\CheckboxType::class, ['required' => false])
            ->add('submit', Type\SubmitType::class)
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'method' => 'POST',
            'csrf_protection' => true,
            'csrf_field_name' => 'token',
            'csrf_token_id' => 'authenticate',
        ]);
    }
}

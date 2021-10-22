<?php

namespace Draw\Bundle\UserBundle\Sonata\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class ChangePasswordForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add(
                'plainPassword',
                RepeatedType::class,
                [
                    'type' => PasswordType::class,
                    'options' => [
                        'attr' => [
                            'autocomplete' => 'new-password',
                        ],
                    ],
                    'first_options' => [
                        'label' => false,
                        'attr' => ['placeholder' => 'form.change_password.field.new_password'],
                    ],
                    'second_options' => [
                        'label' => false,
                        'attr' => ['placeholder' => 'form.change_password.field.new_password_confirmation'],
                    ],
                    'invalid_message' => 'draw_user.form.change_password.password.mismatch',
                ]
            )
            ->add(
                'submit',
                SubmitType::class,
                [
                    'label' => 'form.change_password.field.submit',
                    'attr' => ['class' => 'btn-primary'],
                ]
            );
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'translation_domain' => 'DrawUserBundle',
        ]);
    }
}

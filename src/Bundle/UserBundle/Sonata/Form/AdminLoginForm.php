<?php

namespace Draw\Bundle\UserBundle\Sonata\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class AdminLoginForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add(
                'email',
                EmailType::class,
                [
                    'label' => false,
                    'attr' => [
                        'placeholder' => 'form.authenticate.field.email',
                    ],
                ]
            )
            ->add(
                'password',
                PasswordType::class,
                [
                    'label' => false,
                    'attr' => [
                        'placeholder' => 'form.authenticate.field.password',
                    ],
                ]
            )
            ->add(
                'submit',
                SubmitType::class,
                [
                    'label' => 'form.authenticate.field.submit',
                    'attr' => ['class' => 'btn-primary'],
                ]
            );
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setDefaults([
                'translation_domain' => 'DrawUserBundle',
            ]);
    }
}

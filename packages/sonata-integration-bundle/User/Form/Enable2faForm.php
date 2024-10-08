<?php

namespace Draw\Bundle\SonataIntegrationBundle\User\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

final class Enable2faForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add(
                'code',
                TextType::class,
                [
                    'label' => 'form.enable_2fa.field.code.label',
                    'attr' => [
                        'placeholder' => 'form.enable_2fa.field.code.placeholder',
                    ],
                    'constraints' => [
                        new NotBlank(),
                    ],
                ]
            )
            ->add('totpSecret', HiddenType::class)
            ->add(
                'submit',
                SubmitType::class,
                [
                    'label' => 'form.enable_2fa.field.submit',
                    'attr' => ['class' => 'btn-primary', 'style' => 'float: left;'],
                ]
            )
        ;

        $user = $options['user'] ?? null;

        if ($user && !$user->isForceEnablingTwoFactorAuthentication()) {
            $builder
                ->add(
                    'cancel',
                    SubmitType::class,
                    [
                        'label' => 'form.enable_2fa.field.cancel',
                        'attr' => [
                            'class' => 'btn-danger',
                            'style' => 'float: right;',
                        ],
                        'validation_groups' => false,
                    ]
                )
            ;
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setDefaults([
                'data_class' => Enable2fa::class,
                'translation_domain' => 'DrawUserBundle',
                'user' => null,
                'attr' => [
                    'novalidate' => 'novalidate',
                ],
            ])
        ;
    }
}

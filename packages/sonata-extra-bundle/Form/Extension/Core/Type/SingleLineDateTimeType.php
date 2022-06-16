<?php

namespace Draw\Bundle\SonataExtraBundle\Form\Extension\Core\Type;

use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SingleLineDateTimeType extends DateTimeType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        parent::configureOptions($resolver);

        $resolver->setDefault('widget', 'single_text');
        $resolver->setDefault('input', 'datetime_immutable');
        $resolver->setDefault('format', DateTimeType::HTML5_FORMAT);
    }
}

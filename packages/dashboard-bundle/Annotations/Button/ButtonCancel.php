<?php

namespace Draw\Bundle\DashboardBundle\Annotations\Button;

/**
 * @Annotation
 */
class ButtonCancel extends Button
{
    public function __construct(array $values = [])
    {
        $values = array_merge(
            [
                'id' => 'cancel',
                'label' => 'cancel',
                'style' => 'stroked-button',
                'behaviours' => ['cancel'],
            ],
            $values
        );

        parent::__construct($values);
    }
}

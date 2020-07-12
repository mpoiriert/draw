<?php

namespace Draw\Bundle\DashboardBundle\Annotations\Button;

/**
 * @Annotation
 */
class ButtonDelete extends Button
{
    public function __construct(array $values = [])
    {
        $values = array_merge(
            [
                'id' => 'delete',
                'label' => 'delete',
                'icon' => 'delete',
                'behaviours' => ['delete'],
            ],
            $values
        );
        parent::__construct($values);
    }
}

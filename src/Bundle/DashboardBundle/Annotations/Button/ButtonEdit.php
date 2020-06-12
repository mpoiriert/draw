<?php

namespace Draw\Bundle\DashboardBundle\Annotations\Button;

/**
 * @Annotation
 */
class ButtonEdit extends Button
{
    public function __construct(array $values = [])
    {
        $values = array_merge(
            [
                'label' => 'edit',
                'icon' => 'edit',
            ],
            $values
        );
        parent::__construct($values);
    }
}

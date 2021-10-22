<?php

namespace Draw\Bundle\DashboardBundle\Annotations\Button;

/**
 * @Annotation
 */
class ButtonShow extends Button
{
    public function __construct(array $values = [])
    {
        $values = array_merge(
            [
                'label' => 'show',
            ],
            $values
        );
        parent::__construct($values);
    }
}

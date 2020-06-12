<?php

namespace Draw\Bundle\DashboardBundle\Annotations\Button;

/**
 * @Annotation
 */
class ButtonSaveThenCreate extends ButtonSave
{
    public function __construct(array $values = [])
    {
        $values = array_merge(
            [
                'id' => 'save-then-create',
                'label' => 'saveThenCreate',
                'thenList' => ['create'],
            ],
            $values
        );

        parent::__construct($values);
    }
}

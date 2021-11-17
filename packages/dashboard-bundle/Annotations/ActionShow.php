<?php

namespace Draw\Bundle\DashboardBundle\Annotations;

use Draw\Bundle\DashboardBundle\Annotations\Button as Button;

/**
 * @Annotation
 */
class ActionShow extends Action
{
    public const TYPE = 'show';

    public function __construct(array $values = [])
    {
        $values = array_merge(
            [
                'isInstanceTarget' => true,
                'button' => new Button\ButtonShow(),
            ],
            $values
        );

        parent::__construct($values);
    }
}

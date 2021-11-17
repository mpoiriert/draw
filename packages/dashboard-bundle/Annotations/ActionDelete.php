<?php

namespace Draw\Bundle\DashboardBundle\Annotations;

use Draw\Bundle\DashboardBundle\Annotations\Button as Button;

/**
 * @Annotation
 */
class ActionDelete extends Action
{
    public const TYPE = 'delete';

    public function __construct(array $values = [])
    {
        $values = array_merge(
            [
                'isInstanceTarget' => true,
                'button' => new Button\ButtonDelete(),
                'flow' => new ConfirmFlow([
                    'message' => '_flow.confirm.delete',
                ]),
            ],
            $values
        );

        parent::__construct($values);
    }
}

<?php namespace Draw\Bundle\DashboardBundle\Annotations\Button;

/**
 * @Annotation
 */
class ButtonDelete extends Button
{
    public function __construct(array $values = [])
    {
        $values = array_merge(
            [
                'label' => 'delete',
                'icon' => 'delete'
            ],
            $values
        );
        parent::__construct($values);
    }
}
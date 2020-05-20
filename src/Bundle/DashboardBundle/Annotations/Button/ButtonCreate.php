<?php namespace Draw\Bundle\DashboardBundle\Annotations\Button;

/**
 * @Annotation
 */
class ButtonCreate extends Button
{
    public function __construct(array $values = [])
    {
        $values = array_merge(
            [
                'label' => 'create'
            ],
            $values
        );
        parent::__construct($values);
    }
}
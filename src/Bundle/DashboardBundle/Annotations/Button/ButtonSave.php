<?php namespace Draw\Bundle\DashboardBundle\Annotations\Button;

/**
 * @Annotation
 */
class ButtonSave extends Button
{
    public function __construct(array $values = [])
    {
        $values = array_merge(
            [
                'id' => 'save',
                'label' => 'save',
                'style' => 'flat-button',
                'color' => 'primary',
                'behaviours' => ['submit', 'save'],
                'thenList' => ['edit']
            ],
            $values
        );

        parent::__construct($values);
    }
}
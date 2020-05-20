<?php namespace Draw\Bundle\DashboardBundle\Annotations\Button;

/**
 * @Annotation
 */
class ButtonSaveThenList extends ButtonSave
{
    public function __construct(array $values = [])
    {
        $values = array_merge(
            [
                'id' => 'save-then-list',
                'label' => 'saveThenList',
                'thenList' => ['list']
            ],
            $values
        );

        parent::__construct($values);
    }
}
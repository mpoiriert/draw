<?php namespace Draw\Bundle\DashboardBundle\Annotations;

/**
 * @Annotation
 */
class ActionShow extends Action
{
    public function __construct()
    {
        $this->button = new Button();
        $this->button->label = 'Show';
    }

    public function getType()
    {
        return 'show';
    }
}
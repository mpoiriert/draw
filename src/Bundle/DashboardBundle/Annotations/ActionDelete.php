<?php namespace Draw\Bundle\DashboardBundle\Annotations;

/**
 * @Annotation
 */
class ActionDelete extends Action
{
    public function __construct()
    {
        $this->button = new Button();
        $this->button->label = 'Delete';
        $this->button->icon = 'delete';
        $this->flow = new ConfirmFlow();
        $this->flow->message = 'Are you sure you want to delete this ?';
    }

    public function getType()
    {
        return 'delete';
    }
}
<?php namespace Draw\Bundle\DashboardBundle\Annotations;

/**
 * @Annotation
 */
class ActionEdit extends ActionCreate
{
    const TYPE = 'edit';

    public function __construct()
    {
        parent::__construct();
        $this->button->label = 'Edit';
        $this->button->icon = 'edit';
    }

    public function getType()
    {
        return ActionEdit::TYPE;
    }
}
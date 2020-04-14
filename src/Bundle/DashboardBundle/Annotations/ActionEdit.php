<?php namespace Draw\Bundle\DashboardBundle\Annotations;

/**
 * @Annotation
 */
class ActionEdit extends Action
{
    const TYPE = 'edit';

    public function __construct()
    {
        $this->button = new Button();
        $this->button->label = 'Edit';
        $this->button->icon = 'edit';

        $this->flow = new FormFlow();
        $this->flow->buttons = [
            $save = new Button()
        ];

        $save->label = 'Save';
        $save->behaviours[] = 'submit';
    }

    public function getType()
    {
        return ActionEdit::TYPE;
    }
}
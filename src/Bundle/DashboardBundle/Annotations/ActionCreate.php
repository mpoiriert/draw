<?php namespace Draw\Bundle\DashboardBundle\Annotations;

/**
 * @Annotation
 */
class ActionCreate extends Action
{
    const TYPE = 'create';

    public function __construct()
    {
        $this->button = new Button();
        $this->button->label = 'Create';
        $this->flow = new FormFlow();
        $this->flow->buttons = [
            $save = new Button()
        ];

        $save->label = 'Save';
        $save->behaviours[] = 'submit';
    }

    public function getType()
    {
        return ActionCreate::TYPE;
    }
}
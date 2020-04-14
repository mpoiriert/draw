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
            $cancel = new Button(),
            $save = new Button()
        ];

        $cancel->label = 'Cancel';
        $cancel->style = 'stroked-button';
        $cancel->behaviours[] = 'cancel';

        $save->label = 'Save';
        $save->style = 'flat-button';
        $save->color = 'primary';
        $save->behaviours[] = 'submit';
    }

    public function getType()
    {
        return ActionCreate::TYPE;
    }
}
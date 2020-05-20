<?php namespace Draw\Bundle\DashboardBundle\Annotations;

/**
 * @Annotation
 */
class ActionDelete extends Action
{
    const TYPE = 'delete';

    public function __construct(array $values = [])
    {
        if(!array_key_exists('isInstanceTarget', $values)) {
            $values['isInstanceTarget'] = true;
        }

        if(!array_key_exists('button', $values)) {
            $values['button'] = $button = new Button(['label' => 'delete', 'icon' => 'delete']);
        }

        if(!array_key_exists('flow', $values)) {
            $values['flow'] = $button = new ConfirmFlow([
                'message' => 'Are you sure you want to delete this ?'
            ]);
        }

        parent::__construct($values);
    }
}
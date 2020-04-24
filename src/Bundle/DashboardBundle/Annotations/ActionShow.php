<?php namespace Draw\Bundle\DashboardBundle\Annotations;

/**
 * @Annotation
 */
class ActionShow extends Action
{
    const TYPE = 'show';

    public function __construct(array $values = [])
    {
        if(!array_key_exists('button', $values)) {
            $values['button'] = $button = new Button(['label' => 'show']);
        }

        parent::__construct($values);
    }
}
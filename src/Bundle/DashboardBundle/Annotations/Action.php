<?php namespace Draw\Bundle\DashboardBundle\Annotations;

use Draw\Component\OpenApi\Schema\Vendor;

/**
 * @Annotation
 */
abstract class Action extends Vendor
{
    public $name = 'x-draw-action';

    /**
     * @var \Draw\Bundle\DashboardBundle\Annotations\Button
     */
    public $button;

    /**
     * @var \Draw\Bundle\DashboardBundle\Annotations\Flow
     */
    public $flow;

    /**
     * The class that are a target of this action
     *
     * @var string[]
     */
    public $targets = [];

    public function jsonSerialize()
    {
        $options = parent::jsonSerialize();

        unset($options['targets']);

        $type = $this->getType();

        return compact('type', 'options');
    }

    abstract function getType();
}
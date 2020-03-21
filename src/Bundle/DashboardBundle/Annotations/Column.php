<?php namespace Draw\Bundle\DashboardBundle\Annotations;

use Draw\Component\OpenApi\Schema\Vendor;

/**
 * @Annotation
 */
class Column extends Vendor
{
    public $name = "x-draw-column";

    /**
     * @var string
     */
    public $id;

    /**
     * @var bool
     */
    public $isActive = true;

    /**
     * @var string
     */
    public $label;

    /**
     * @var bool
     */
    public $sortable;

    /**
     * @var bool
     */
    public $visible = true;

    /**
     * @var string
     */
    public $type = 'simple';

    /**
     * @var array
     */
    public $options;
}
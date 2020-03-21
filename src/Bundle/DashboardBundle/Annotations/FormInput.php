<?php namespace Draw\Bundle\DashboardBundle\Annotations;

use Draw\Component\OpenApi\Schema\Vendor;

/**
 * @Annotation
 */
class FormInput extends Vendor
{
    public $name = 'x-draw-form-input';

    /**
     * @var string
     */
    public $id;

    /**
     * @var string
     */
    public $type = 'text';

    /**
     * @var string
     */
    public $label;

    /**
     * @var string
     */
    public $icon;
}
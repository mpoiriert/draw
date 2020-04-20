<?php namespace Draw\Bundle\DashboardBundle\Annotations;

/**
 * @Annotation
 */
class FormInputCollection extends FormInput
{
    /**
     * @var string
     */
    public $type = 'collection';

    /**
     * @var string
     */
    public $orderBy = null;
}
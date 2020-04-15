<?php namespace Draw\Bundle\DashboardBundle\Annotations;

/**
 * @Annotation
 */
class FormInputChoices extends FormInput
{
    /**
     * @var string
     */
    public $type = 'choices';

    /**
     * @var bool
     */
    public $multiple = false;

    /**
     * @var string
     */
    public $repositoryMethod = null;
}
<?php namespace Draw\Bundle\DashboardBundle\Annotations;

/**
 * @Annotation
 */
class FormFlow extends Flow
{
    public $type = 'form';

    /**
     * @var array<\Draw\Bundle\DashboardBundle\Annotations\Button>
     */
    public $buttons;
}
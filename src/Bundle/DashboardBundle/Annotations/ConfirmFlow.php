<?php namespace Draw\Bundle\DashboardBundle\Annotations;

/**
 * @Annotation
 */
class ConfirmFlow extends Flow
{
    public $type = 'confirm';
    public $title = '';
    public $message = 'Are you sure ?';
}
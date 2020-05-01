<?php namespace Draw\Bundle\DashboardBundle\Annotations;

use JMS\Serializer\Annotation as Serializer;

/**
 * @Annotation
 */
class FormInputDatePicker extends FormInput
{
    const TYPE = 'date-picker';
}
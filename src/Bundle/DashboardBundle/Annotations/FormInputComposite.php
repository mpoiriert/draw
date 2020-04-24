<?php namespace Draw\Bundle\DashboardBundle\Annotations;

/**
 * @Annotation
 */
class FormInputComposite extends FormInput
{
    const TYPE = 'composite';

    private $subForm;

    public function getSubForm()
    {
        return $this->subForm;
    }

    public function setSubForm($subForm): void
    {
        $this->subForm = $subForm;
    }
}
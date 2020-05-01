<?php namespace Draw\Bundle\DashboardBundle\Annotations;

use JMS\Serializer\Annotation as Serializer;

/**
 * @Annotation
 */
class FormInputComposite extends FormInput
{
    const TYPE = 'composite';

    /**
     * @Serializer\SerializedName("subForm")
     */
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
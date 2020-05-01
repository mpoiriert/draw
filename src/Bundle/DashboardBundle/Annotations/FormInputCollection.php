<?php namespace Draw\Bundle\DashboardBundle\Annotations;

use JMS\Serializer\Annotation as Serializer;

/**
 * @Annotation
 */
class FormInputCollection extends FormInput
{
    const TYPE = 'collection';

    /**
     * @var string|null
     *
     * @Serializer\SerializedName("orderBy")
     */
    private $orderBy = null;

    /**
     * @Serializer\SerializedName("subForm")
     */
    private $subForm;

    public function getOrderBy(): ?string
    {
        return $this->orderBy;
    }

    public function setOrderBy(?string $orderBy): void
    {
        $this->orderBy = $orderBy;
    }

    public function getSubForm()
    {
        return $this->subForm;
    }

    public function setSubForm($subForm): void
    {
        $this->subForm = $subForm;
    }
}
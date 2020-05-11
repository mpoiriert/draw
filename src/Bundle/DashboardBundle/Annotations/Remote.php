<?php namespace Draw\Bundle\DashboardBundle\Annotations;

/**
 * @Annotation
 */
class Remote extends BaseAnnotation
{
    /**
     * @var string|null
     */
    private $routeName;

    /**
     * @var string|null
     */
    private $formPathValue;

    public function getRouteName(): ?string
    {
        return $this->routeName;
    }

    public function setRouteName(?string $routeName): void
    {
        $this->routeName = $routeName;
    }

    public function getFormPathValue(): ?string
    {
        return $this->formPathValue;
    }

    public function setFormPathValue(?string $formPathValue): void
    {
        $this->formPathValue = $formPathValue;
    }
}
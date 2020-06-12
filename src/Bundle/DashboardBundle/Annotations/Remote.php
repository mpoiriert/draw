<?php

namespace Draw\Bundle\DashboardBundle\Annotations;

use function Draw\Bundle\DashboardBundle\construct;

/**
 * @Annotation
 */
class Remote
{
    /**
     * @var string|null
     */
    private $routeName;

    /**
     * @var string|null
     */
    private $formPathValue;

    public function __construct(array $values = [])
    {
        construct($this, $values);
    }

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

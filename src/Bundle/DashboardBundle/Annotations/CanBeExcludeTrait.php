<?php namespace Draw\Bundle\DashboardBundle\Annotations;

use JMS\Serializer\Annotation as Serializer;

trait CanBeExcludeTrait
{
    /**
     * @var string|null
     *
     * @Serializer\Exclude()
     */
    private $excludeIf;

    public function getExcludeIf(): ?string
    {
        return $this->excludeIf;
    }

    public function setExcludeIf(?string $excludeIf): void
    {
        $this->excludeIf = $excludeIf;
    }
}
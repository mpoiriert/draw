<?php namespace Draw\Bundle\DashboardBundle\Annotations;

use Draw\Component\OpenApi\Schema\VendorInterface;

/**
 * @Annotation
 */
class Targets extends BaseAnnotation implements VendorInterface
{
    /**
     * @var array<string>
     */
    private $targets = [];

    public function getVendorName(): string
    {
        return 'x-draw-dashboard-targets';
    }

    public function allowClassLevelConfiguration(): bool
    {
        return true;
    }

    public function setValue(array $value)
    {
        $this->setTargets($value);
    }

    public function getTargets(): array
    {
        return $this->targets;
    }

    public function setTargets(array $targets): void
    {
        $this->targets = $targets;
    }
}
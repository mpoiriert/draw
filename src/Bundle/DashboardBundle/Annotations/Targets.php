<?php namespace Draw\Bundle\DashboardBundle\Annotations;

use Draw\Component\OpenApi\Schema\VendorInterface;
use function Draw\Bundle\DashboardBundle\construct;

/**
 * @Annotation
 */
class Targets implements VendorInterface
{
    /**
     * @var array<string>
     */
    private $targets = [];

    public function __construct(array $values = [])
    {
        construct($this, $values);
    }

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
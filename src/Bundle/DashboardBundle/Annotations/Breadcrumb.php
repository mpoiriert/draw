<?php

namespace Draw\Bundle\DashboardBundle\Annotations;

use function Draw\Bundle\DashboardBundle\construct;
use Draw\Component\OpenApi\Schema\VendorInterface;

/**
 * @Annotation
 */
class Breadcrumb implements VendorInterface
{
    /**
     * @var string|null
     */
    private $parentOperationId;

    private $label;

    public function __construct(array $values = [])
    {
        construct($this, $values);
    }

    public function getVendorName(): string
    {
        return 'x-draw-dashboard-breadcrumb';
    }

    public function allowClassLevelConfiguration(): bool
    {
        return true;
    }

    public function getParentOperationId(): ?string
    {
        return $this->parentOperationId;
    }

    public function setParentOperationId(?string $parentOperationId): void
    {
        $this->parentOperationId = $parentOperationId;
    }

    public function getLabel()
    {
        return $this->label;
    }

    public function setLabel($label): void
    {
        $this->label = Translatable::set($this->label, $label);
    }
}

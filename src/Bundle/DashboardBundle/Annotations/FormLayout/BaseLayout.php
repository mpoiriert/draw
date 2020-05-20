<?php namespace Draw\Bundle\DashboardBundle\Annotations\FormLayout;

use Draw\Bundle\DashboardBundle\Annotations\BaseAnnotation;
use Draw\Component\OpenApi\Schema\VendorInterface;

/**
 * @Annotation
 */
class BaseLayout extends BaseAnnotation implements VendorInterface
{
    const TYPE = 'generic';

    /**
     * @var string|null
     */
    private $type = self::TYPE;

    public function __construct(array $values = [])
    {
        $values = array_merge(
            [
                'type' => static::TYPE
            ],
            $values
        );

        parent::__construct($values);
    }

    public function getVendorName(): string
    {
        return 'x-draw-dashboard-form-layout';
    }

    public function allowClassLevelConfiguration(): bool
    {
        return false;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(?string $type): void
    {
        $this->type = $type;
    }
}
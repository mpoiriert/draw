<?php namespace Draw\Bundle\DashboardBundle\Annotations;

use JMS\Serializer\Annotation as Serializer;
use function Draw\Bundle\DashboardBundle\construct;

/**
 * @Annotation
 */
class Column implements VendorPropertyInterface, CanBeExcludeInterface
{
    use CanBeExcludeTrait;

    /**
     * @var string|null
     */
    private $id;

    /**
     * @var bool
     */
    private $isActive = true;

    /**
     * @var string|null
     */
    private $label;

    /**
     * @var bool
     */
    private $sortable;

    /**
     * @var bool
     */
    private $visible = true;

    /**
     * @var string
     */
    private $type = 'simple';

    /**
     * @var int|null
     *
     * @Serializer\Exclude()
     */
    private $position;

    /**
     * @var array
     */
    private $options;

    public function __construct(array $values = [])
    {
        construct($this, $values);
    }

    public function getVendorName(): string
    {
        return 'x-draw-dashboard-column';
    }

    public function allowClassLevelConfiguration(): bool
    {
        return false;
    }

    public function getId(): ?string
    {
        return $this->id;
    }

    public function setId(?string $id): void
    {
        $this->id = $id;
    }

    public function isActive(): bool
    {
        return $this->isActive;
    }

    public function setIsActive(bool $isActive): void
    {
        $this->isActive = $isActive;
    }

    public function getLabel()
    {
        return $this->label;
    }

    public function setLabel($label): void
    {
        $this->label = Translatable::set($this->label, $label);
    }

    public function isSortable(): bool
    {
        return $this->sortable;
    }

    public function setSortable(bool $sortable): void
    {
        $this->sortable = $sortable;
    }

    public function isVisible(): bool
    {
        return $this->visible;
    }

    public function setVisible(bool $visible): void
    {
        $this->visible = $visible;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function setType(string $type): void
    {
        $this->type = $type;
    }

    public function getOptions(): array
    {
        return $this->options;
    }

    public function setOptions(array $options): void
    {
        $this->options = $options;
    }

    public function getPosition(): ?int
    {
        return $this->position;
    }

    public function setPosition(?int $position): void
    {
        $this->position = $position;
    }
}
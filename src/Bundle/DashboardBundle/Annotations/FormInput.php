<?php namespace Draw\Bundle\DashboardBundle\Annotations;

use function Draw\Bundle\DashboardBundle\construct;

/**
 * @Annotation
 */
class FormInput implements VendorPropertyInterface
{
    const TYPE = 'text';

    /**
     * @var string|null
     */
    private $id;

    /**
     * @var string
     */
    private $type = 'text';

    /**
     * @var string|null
     */
    private $label;

    /**
     * @var string|null
     */
    private $icon;

    /**
     * @var bool
     */
    private $disabled = false;

    public function __construct(array $values = [])
    {
        if (!array_key_exists('type', $values)) {
            $values['type'] = static::TYPE;
        }

        construct($this, $values);
    }

    public function getVendorName(): string
    {
        return 'x-draw-dashboard-form-input';
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

    public function getType(): string
    {
        return $this->type;
    }

    public function setType(string $type): void
    {
        $this->type = $type;
    }

    public function getLabel()
    {
        return $this->label;
    }

    public function setLabel($label): void
    {
        $this->label = Translatable::set($this->label, $label);
    }

    public function getIcon(): ?string
    {
        return $this->icon;
    }

    public function setIcon(?string $icon): void
    {
        $this->icon = $icon;
    }

    public function isDisabled(): bool
    {
        return $this->disabled;
    }

    public function setDisabled(bool $disabled): void
    {
        $this->disabled = $disabled;
    }
}
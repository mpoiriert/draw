<?php

namespace Draw\Bundle\DashboardBundle\Annotations;

use function Draw\Bundle\DashboardBundle\construct;
use JMS\Serializer\Annotation as Serializer;

/**
 * @Annotation
 */
class FormInput implements VendorPropertyInterface, CanBeExcludeInterface
{
    use CanBeExcludeTrait;

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

    /**
     * @var bool
     */
    private $required = false;

    /**
     * @var int|null
     *
     * @Serializer\Exclude()
     */
    private $position;

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

    public function getDisabled(): bool
    {
        return $this->disabled;
    }

    public function setDisabled(bool $disabled): void
    {
        $this->disabled = $disabled;
    }

    public function getRequired(): bool
    {
        return $this->required;
    }

    public function setRequired(bool $required): void
    {
        $this->required = $required;
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

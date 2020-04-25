<?php namespace Draw\Bundle\DashboardBundle\Annotations;

use Draw\Bundle\DashboardBundle\BaseAnnotation;

/**
 * @Annotation
 */
class FormInput extends BaseAnnotation implements VendorPropertyInterface
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

    public function __construct(array $values = [])
    {
        if (!array_key_exists('type', $values)) {
            $values['type'] = static::TYPE;
        }

        parent::__construct($values);
    }

    public function getVendorName(): string
    {
        return 'x-draw-dashboard-form-input';
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
}
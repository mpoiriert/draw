<?php namespace Draw\Bundle\DashboardBundle\Annotations;

use Doctrine\Common\Annotations\Annotation\Enum;
use Draw\Bundle\DashboardBundle\BaseAnnotation;
use JMS\Serializer\Annotation as Serializer;

/**
 * @Annotation
 */
class Button extends BaseAnnotation
{
    private $label;

    /**
     * @var string|null
     */
    private $icon;

    /**
     * @var string|null
     */
    private $style;

    /**
     * @var string|null
     */
    private $color;

    /**
     * @var bool|null
     *
     * @Serializer\SerializedName("showLabel")
     */
    private $showLabel;

    /**
     * @var string|null
     */
    private $tooltip;

    /**
     * @var string|null
     *
     * @Enum({"left", "right", "above", "below", "before", "after"})
     *
     * @Serializer\SerializedName("tooltipPosition")
     */
    private $tooltipPosition;

    /**
     * @var array<string>
     */
    public $behaviours = [];

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

    public function getStyle(): ?string
    {
        return $this->style;
    }

    public function setStyle(?string $style): void
    {
        $this->style = $style;
    }

    public function getColor(): ?string
    {
        return $this->color;
    }

    public function setColor(?string $color): void
    {
        $this->color = $color;
    }

    public function getShowLabel(): ?bool
    {
        return $this->showLabel;
    }

    public function setShowLabel(?bool $showLabel): void
    {
        $this->showLabel = $showLabel;
    }

    public function getTooltip(): ?string
    {
        return $this->tooltip;
    }

    public function setTooltip(?string $tooltip): void
    {
        $this->tooltip = $tooltip;
    }

    public function getTooltipPosition(): ?string
    {
        return $this->tooltipPosition;
    }

    public function setTooltipPosition(?string $tooltipPosition): void
    {
        $this->tooltipPosition = $tooltipPosition;
    }

    public function getBehaviours(): array
    {
        return $this->behaviours;
    }

    public function setBehaviours(array $behaviours): void
    {
        $this->behaviours = $behaviours;
    }
}
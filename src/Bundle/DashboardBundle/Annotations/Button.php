<?php namespace Draw\Bundle\DashboardBundle\Annotations;

use Doctrine\Common\Annotations\Annotation\Enum;
use JMS\Serializer\Annotation as Serializer;

/**
 * @Annotation
 */
class Button
{
    public $label;

    public $icon;

    public $style;

    public $color;

    /**
     * @Serializer\SerializedName("showLabel")
     */
    public $showLabel;

    public $tooltip;

    /**
     * @Enum({"left", "right", "above", "below", "before", "after"})
     *
     * @Serializer\SerializedName("tooltipPosition")
     */
    public $tooltipPosition;

    /**
     * @var array<string>
     */
    public $behaviours = [];
}
<?php

namespace Draw\Bundle\DashboardBundle\Annotations;

use JMS\Serializer\Annotation as Serializer;

/**
 * @Annotation
 */
class FormInputChoices extends FormInput
{
    const TYPE = 'choices';

    /**
     * @var bool
     */
    private $multiple = false;

    private $choices = null;

    /**
     * @var array|null
     *
     * @Serializer\SerializedName("sourceCompareKeys")
     */
    private $sourceCompareKeys = null;

    /**
     * @var bool
     *
     * @Serializer\SerializedName("autoSelect")
     */
    private $autoSelect = false;

    /**
     * @var string|null
     *
     * @Serializer\Exclude()
     */
    private $expression;

    public function getMultiple(): bool
    {
        return $this->multiple;
    }

    public function setMultiple(bool $multiple): void
    {
        $this->multiple = $multiple;
    }

    public function getChoices()
    {
        return $this->choices;
    }

    public function setChoices($choices): void
    {
        if ($choices instanceof Remote) {
            $this->choices = $choices;

            return;
        }

        if (!$choices instanceof Choices) {
            $choices = new Choices(['choices' => $choices]);
        }

        $this->choices = $choices->toArray();
    }

    public function getSourceCompareKeys(): ?array
    {
        return $this->sourceCompareKeys;
    }

    public function setSourceCompareKeys(?array $sourceCompareKeys): void
    {
        $this->sourceCompareKeys = $sourceCompareKeys;
    }

    public function getExpression(): ?string
    {
        return $this->expression;
    }

    public function setExpression(?string $expression): void
    {
        $this->expression = $expression;
    }

    public function getAutoSelect(): bool
    {
        return $this->autoSelect;
    }

    public function setAutoSelect(bool $autoSelect): void
    {
        $this->autoSelect = $autoSelect;
    }
}

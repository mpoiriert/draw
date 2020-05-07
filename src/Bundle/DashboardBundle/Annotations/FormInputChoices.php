<?php namespace Draw\Bundle\DashboardBundle\Annotations;

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

    /**
     * @var string|null
     *
     * @Serializer\Exclude()
     */
    private $repositoryMethod = null;

    /**
     * @var array|null
     */
    private $choices = null;

    /**
     * @var array|null
     *
     * @Serializer\SerializedName("sourceCompareKeys")
     */
    private $sourceCompareKeys = null;

    public function getMultiple(): bool
    {
        return $this->multiple;
    }

    public function setMultiple(bool $multiple): void
    {
        $this->multiple = $multiple;
    }

    public function getRepositoryMethod(): ?string
    {
        return $this->repositoryMethod;
    }

    public function setRepositoryMethod(?string $repositoryMethod): void
    {
        $this->repositoryMethod = $repositoryMethod;
    }

    public function getChoices(): ?array
    {
        return $this->choices;
    }

    public function setChoices($choices): void
    {
        if(!$choices instanceof Choices) {
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
}
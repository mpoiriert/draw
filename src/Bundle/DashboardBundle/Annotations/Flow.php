<?php namespace Draw\Bundle\DashboardBundle\Annotations;

/**
 * @Annotation
 */
abstract class Flow extends BaseAnnotation
{
    const TYPE = null;

    /**
     * @var string|null
     */
    private $type;

    public function __construct(array $values)
    {
        $values['type'] = static::TYPE;

        parent::__construct($values);
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
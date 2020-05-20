<?php namespace Draw\Bundle\DashboardBundle\Annotations;

use function Draw\Bundle\DashboardBundle\construct;

/**
 * @Annotation
 */
abstract class Flow
{
    const TYPE = null;

    /**
     * @var string|null
     */
    private $type;

    public function __construct(array $values = [])
    {
        $values['type'] = static::TYPE;

        construct($this, $values);
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
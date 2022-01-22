<?php

namespace Draw\Component\Messenger\Stamp;

use Symfony\Component\Messenger\Stamp\StampInterface;

class SearchableTagStamp implements StampInterface
{
    /**
     * @var array|string[]
     */
    private $tags;

    /**
     * @var bool
     */
    private $enforceUniqueness = false;

    public function __construct(array $tags, bool $enforceUniqueness = false)
    {
        $this->tags = array_values(array_unique(array_filter($tags)));
        $this->enforceUniqueness = $enforceUniqueness;
    }

    public function getTags(): array
    {
        return $this->tags;
    }

    /**
     * When true the other envelopes with matching tags must be removed.
     */
    public function getEnforceUniqueness(): bool
    {
        return $this->enforceUniqueness;
    }
}

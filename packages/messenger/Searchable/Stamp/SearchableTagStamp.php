<?php

namespace Draw\Component\Messenger\Searchable\Stamp;

use Symfony\Component\Messenger\Stamp\StampInterface;

class SearchableTagStamp implements StampInterface
{
    /**
     * @var array|string[]
     */
    private array $tags;

    public function __construct(
        array $tags,
        // Keep false as the default value since some stamp can be deserialized
        private bool $enforceUniqueness = false
    ) {
        $this->tags = array_values(array_unique(array_filter($tags)));
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

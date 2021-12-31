<?php

namespace Draw\Component\Messenger\Stamp;

use Symfony\Component\Messenger\Stamp\StampInterface;

class SearchableTagStamp implements StampInterface
{
    /**
     * @var array
     */
    private $tags;

    public function __construct(array $tags)
    {
        $this->tags = $tags;
    }

    public function getTags(): array
    {
        return $this->tags;
    }
}

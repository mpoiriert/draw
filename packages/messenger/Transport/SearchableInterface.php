<?php

namespace Draw\Component\Messenger\Transport;

use Symfony\Component\Messenger\Envelope;

interface SearchableInterface
{
    /**
     * @return array|Envelope[]
     */
    public function findByTag(string $tag): array;

    /**
     * Return all envelop that match all tags.
     *
     * @return array|Envelope[]
     */
    public function findByTags(array $tags): array;
}

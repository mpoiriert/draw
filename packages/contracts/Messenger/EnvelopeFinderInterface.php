<?php

namespace Draw\Contracts\Messenger;

use Draw\Contracts\Messenger\Exception\MessageNotFoundException;
use Symfony\Component\Messenger\Envelope;

interface EnvelopeFinderInterface
{
    /**
     * @throws MessageNotFoundException
     */
    public function findById(string $messageId): Envelope;

    /**
     * Return all envelop that match all tags.
     *
     * @param array|string[] $tags
     *
     * @return array|Envelope[]
     */
    public function findByTags(array $tags): array;
}

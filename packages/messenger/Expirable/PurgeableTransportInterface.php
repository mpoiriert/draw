<?php

namespace Draw\Component\Messenger\Expirable;

use Symfony\Component\Messenger\Transport\TransportInterface;

interface PurgeableTransportInterface extends TransportInterface
{
    /**
     * Purge message that are obsolete (expired) since a specific date.
     *
     * Return the amount of message that have been purged.
     */
    public function purgeObsoleteMessages(\DateTimeInterface $since): int;
}

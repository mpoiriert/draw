<?php

namespace Draw\Component\Messenger\Transport;

use DateTimeInterface;

interface ObsoleteMessageAwareInterface
{
    /**
     * Purge message that are obsolete (expired) since a specific date.
     *
     * Return the amount of message that have been purged.
     */
    public function purgeObsoleteMessages(DateTimeInterface $since): int;
}

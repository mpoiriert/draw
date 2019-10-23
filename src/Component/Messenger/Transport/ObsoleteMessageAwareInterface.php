<?php namespace Draw\Component\Messenger\Transport;

interface ObsoleteMessageAwareInterface
{
    /**
     * Purge message that are obsolete (expired) since a specific date.
     *
     * Return the amount of message that have been purged.
     *
     * @param \DateTimeInterface|null $since
     * @return int
     */
    public function purgeObsoleteMessages(\DateTimeInterface $since): int;
}
<?php

namespace Draw\Component\Messenger\Retry\Message;

use Symfony\Component\Messenger\Envelope;

interface SelfAwareRetryableMessageInterface
{
    /**
     * Return the waiting time in milliseconds for the next retry or null if no retry is needed.
     */
    public function getRetryWaitingTime(Envelope $message, \Throwable $throwable, int $currentRetryCount): ?int;
}

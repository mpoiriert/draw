<?php

namespace Draw\Component\Messenger\Retry;

use Draw\Component\Messenger\Retry\Event\GetWaitingTimeEvent;
use Draw\Component\Messenger\Retry\Event\IsRetryableEvent;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Retry\RetryStrategyInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class EventDrivenRetryStrategy implements RetryStrategyInterface
{
    public function __construct(
        private EventDispatcherInterface $eventDispatcher,
        private ?RetryStrategyInterface $fallbackRetryStrategy = null
    ) {
    }

    public function isRetryable(Envelope $message): bool
    {
        $isRetryable = $this->eventDispatcher
            ->dispatch(new IsRetryableEvent($message))
            ->getIsRetryable();

        return $isRetryable ?? $this->fallbackRetryStrategy?->isRetryable($message) ?? false;
    }

    public function getWaitingTime(Envelope $message): int
    {
        $waitingTime = $this->eventDispatcher
            ->dispatch(new GetWaitingTimeEvent($message))
            ->getWaitingTime();

        return $waitingTime ?? $this->fallbackRetryStrategy?->getWaitingTime($message) ?? 1000;
    }
}

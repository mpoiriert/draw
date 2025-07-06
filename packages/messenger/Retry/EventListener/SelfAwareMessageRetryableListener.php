<?php

namespace Draw\Component\Messenger\Retry\EventListener;

use Draw\Component\Messenger\Retry\Event\GetWaitingTimeEvent;
use Draw\Component\Messenger\Retry\Event\IsRetryableEvent;
use Draw\Component\Messenger\Retry\Message\SelfAwareRetryableMessageInterface;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\Messenger\Stamp\RedeliveryStamp;
use Symfony\Contracts\Service\ResetInterface;

class SelfAwareMessageRetryableListener implements ResetInterface
{
    /**
     * @var array <string, int>
     */
    private array $waitingTimes = [];

    #[AsEventListener(priority: 1)]
    public function onIsRetryableEvent(IsRetryableEvent $event): void
    {
        $envelope = $event->getEnvelope();

        $message = $envelope->getMessage();

        if (!$message instanceof SelfAwareRetryableMessageInterface) {
            return;
        }

        $retries = RedeliveryStamp::getRetryCountFromEnvelope($envelope);

        $waitingTime = $message->getRetryWaitingTime($envelope, $event->getThrowable(), $retries);

        if (null === $waitingTime) {
            $event->setIsRetryable(false);

            return;
        }

        $event->setIsRetryable(true);

        $this->waitingTimes[spl_object_hash($envelope)] = $waitingTime;
    }

    #[AsEventListener(priority: 1)]
    public function onGetWaitingTimeEvent(GetWaitingTimeEvent $event): void
    {
        $envelope = $event->getEnvelope();

        if (!isset($this->waitingTimes[spl_object_hash($envelope)])) {
            return;
        }

        $event->setWaitingTime($this->waitingTimes[spl_object_hash($envelope)]);
    }

    public function reset(): void
    {
        $this->waitingTimes = [];
    }
}

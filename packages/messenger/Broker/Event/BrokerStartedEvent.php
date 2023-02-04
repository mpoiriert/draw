<?php

namespace Draw\Component\Messenger\Broker\Event;

use Draw\Component\Messenger\Broker\Broker;
use Symfony\Contracts\EventDispatcher\Event;

class BrokerStartedEvent extends Event
{
    public function __construct(
        private Broker $broker,
        private int $concurrent,
        private int $timeout
    ) {
    }

    public function getBroker(): Broker
    {
        return $this->broker;
    }

    public function getConcurrent(): int
    {
        return $this->concurrent;
    }

    public function getTimeout(): int
    {
        return $this->timeout;
    }
}

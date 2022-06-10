<?php

namespace Draw\Component\Messenger\Broker\Event;

use Draw\Component\Messenger\Broker\Broker;
use Symfony\Contracts\EventDispatcher\Event;

class BrokerStartedEvent extends Event
{
    private Broker $broker;

    private int $concurrent;

    private int $timeout;

    public function __construct(Broker $broker, int $concurrent, int $timeout)
    {
        $this->broker = $broker;
        $this->concurrent = $concurrent;
        $this->timeout = $timeout;
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

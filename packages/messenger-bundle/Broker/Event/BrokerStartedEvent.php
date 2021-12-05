<?php

namespace Draw\Bundle\MessengerBundle\Broker\Event;

use Draw\Bundle\MessengerBundle\Broker\Broker;
use Symfony\Contracts\EventDispatcher\Event;

class BrokerStartedEvent extends Event
{
    private $broker;

    private $concurrent;

    private $timeout;

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

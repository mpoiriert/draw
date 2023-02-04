<?php

namespace Draw\Component\Messenger\Broker\Event;

use Draw\Component\Messenger\Broker\Broker;
use Symfony\Contracts\EventDispatcher\Event;

class BrokerRunningEvent extends Event
{
    public function __construct(private Broker $broker)
    {
    }

    public function getBroker(): Broker
    {
        return $this->broker;
    }
}

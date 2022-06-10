<?php

namespace Draw\Component\Messenger\Broker\Event;

use Draw\Component\Messenger\Broker\Broker;
use Symfony\Contracts\EventDispatcher\Event;

class BrokerRunningEvent extends Event
{
    private Broker $broker;

    public function __construct(Broker $broker)
    {
        $this->broker = $broker;
    }

    public function getBroker(): Broker
    {
        return $this->broker;
    }
}

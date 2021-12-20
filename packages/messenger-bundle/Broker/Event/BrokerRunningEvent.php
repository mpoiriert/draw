<?php

namespace Draw\Bundle\MessengerBundle\Broker\Event;

use Draw\Bundle\MessengerBundle\Broker\Broker;
use Symfony\Contracts\EventDispatcher\Event;

class BrokerRunningEvent extends Event
{
    private $broker;

    public function __construct(Broker $broker)
    {
        $this->broker = $broker;
    }

    public function getBroker(): Broker
    {
        return $this->broker;
    }
}

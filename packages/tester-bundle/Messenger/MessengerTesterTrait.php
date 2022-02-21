<?php

namespace Draw\Bundle\TesterBundle\Messenger;

use Draw\Bundle\TesterBundle\DependencyInjection\ServiceTesterTrait;

trait MessengerTesterTrait
{
    use ServiceTesterTrait;

    public static function getTransportTester(string $transportName): TransportTester
    {
        return static::getService(sprintf('messenger.transport.%s.draw.tester', $transportName));
    }
}

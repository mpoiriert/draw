<?php

namespace Draw\Bundle\TesterBundle\Messenger;

trait MessengerTesterTrait
{
    public static function getTransportTester(string $transportName): TransportTester
    {
        return static::getContainer()->get(sprintf('messenger.transport.%s.draw.tester', $transportName));
    }
}

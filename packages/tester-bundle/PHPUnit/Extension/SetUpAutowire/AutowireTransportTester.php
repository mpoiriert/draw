<?php

namespace Draw\Bundle\TesterBundle\PHPUnit\Extension\SetUpAutowire;

#[\Attribute(\Attribute::TARGET_PROPERTY)]
class AutowireTransportTester extends AutowireService
{
    public function __construct(string $tranportName)
    {
        parent::__construct(sprintf('messenger.transport.%s.draw.tester', $tranportName));
    }
}

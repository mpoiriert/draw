<?php

namespace Draw\Bundle\DoctrineBusMessageBundle\Tests\DependencyInjection;

use Draw\Bundle\DoctrineBusMessageBundle\DependencyInjection\DrawDoctrineBusMessageEventExtension;
use Draw\Bundle\DoctrineBusMessageBundle\Listener\DoctrineBusMessageEventSubscriber;
use Draw\Component\Tester\DependencyInjection\ExtensionTestCase;
use Symfony\Component\DependencyInjection\Extension\Extension;

class DrawDoctrineBusMessageEventExtensionTest extends ExtensionTestCase
{
    public function createExtension(): Extension
    {
        return new DrawDoctrineBusMessageEventExtension();
    }

    public function getConfiguration(): array
    {
        return [];
    }

    public function provideTestHasServiceDefinition(): iterable
    {
        yield [DoctrineBusMessageEventSubscriber::class];
    }
}

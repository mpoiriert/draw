<?php

namespace Draw\Bundle\DoctrineBusMessageBundle\Tests\DependencyInjection;

use Draw\Bundle\DoctrineBusMessageBundle\DependencyInjection\DrawDoctrineBusMessageExtension;
use Draw\Bundle\DoctrineBusMessageBundle\Listener\DoctrineBusMessageEventSubscriber;
use Draw\Component\Tester\DependencyInjection\ExtensionTestCase;
use Symfony\Component\DependencyInjection\Extension\Extension;

class DrawDoctrineBusMessageExtensionTest extends ExtensionTestCase
{
    public function createExtension(): Extension
    {
        return new DrawDoctrineBusMessageExtension();
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

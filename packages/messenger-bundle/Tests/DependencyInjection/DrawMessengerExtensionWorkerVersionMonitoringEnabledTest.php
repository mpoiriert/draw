<?php

namespace Draw\Bundle\MessengerBundle\Tests\DependencyInjection;

use Draw\Bundle\MessengerBundle\DependencyInjection\DrawMessengerExtension;
use Draw\Bundle\MessengerBundle\EventListener\StopWorkerOnNewVersionListener;
use Symfony\Component\DependencyInjection\Extension\Extension;

class DrawMessengerExtensionWorkerVersionMonitoringEnabledTest extends DrawMessengerExtensionTest
{
    public function createExtension(): Extension
    {
        return new DrawMessengerExtension();
    }

    public function getConfiguration(): array
    {
        return [
            'worker_version_monitoring' => [
                'enabled' => true,
            ],
        ];
    }

    public function provideTestHasServiceDefinition(): iterable
    {
        yield from parent::provideTestHasServiceDefinition();
        yield [StopWorkerOnNewVersionListener::class];
    }
}

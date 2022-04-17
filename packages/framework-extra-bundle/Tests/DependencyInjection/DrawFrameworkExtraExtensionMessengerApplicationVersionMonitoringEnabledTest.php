<?php

namespace Draw\Bundle\FrameworkExtraBundle\Tests\DependencyInjection;

use Draw\Component\Messenger\EventListener\StopOnNewVersionListener;

class DrawFrameworkExtraExtensionMessengerApplicationVersionMonitoringEnabledTest extends DrawFrameworkExtraExtensionMessengerTest
{
    public function getConfiguration(): array
    {
        $configuration = parent::getConfiguration();

        $configuration['messenger']['application_version_monitoring'] = [
            'enabled' => true,
        ];

        return $configuration;
    }

    public function provideTestHasServiceDefinition(): iterable
    {
        yield from parent::provideTestHasServiceDefinition();
        yield ['draw.messenger.stop_on_new_version_listener'];
        yield [StopOnNewVersionListener::class, 'draw.messenger.stop_on_new_version_listener'];
    }
}

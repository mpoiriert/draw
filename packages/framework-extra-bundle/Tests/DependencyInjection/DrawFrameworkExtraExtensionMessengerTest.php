<?php

namespace Draw\Bundle\FrameworkExtraBundle\Tests\DependencyInjection;

use Draw\Component\Messenger\Broker;
use Draw\Component\Messenger\Command\PurgeExpiredMessageCommand;
use Draw\Component\Messenger\Controller\MessageController;
use Draw\Component\Messenger\EnvelopeFinder;
use Draw\Component\Messenger\EventListener\AutoStampEnvelopeListener;
use Draw\Component\Messenger\ManuallyTriggeredMessageUrlGenerator;
use Draw\Component\Messenger\Message\AsyncHighPriorityMessageInterface;
use Draw\Component\Messenger\Message\AsyncLowPriorityMessageInterface;
use Draw\Component\Messenger\Message\AsyncMessageInterface;
use Draw\Component\Messenger\MessageHandler\RedirectToRouteMessageHandler;
use Draw\Component\Messenger\Transport\DrawTransportFactory;
use ReflectionClass;

class DrawFrameworkExtraExtensionMessengerTest extends DrawFrameworkExtraExtensionTest
{
    public function getConfiguration(): array
    {
        $configuration = parent::getConfiguration();

        $configuration['messenger'] = [
            'async_routing_configuration' => [
                'enabled' => true,
            ],
        ];

        return $configuration;
    }

    public function provideTestHasServiceDefinition(): iterable
    {
        yield from parent::provideTestHasServiceDefinition();
        yield ['draw.messenger.draw_transport_factory'];
        yield [DrawTransportFactory::class, 'draw.messenger.draw_transport_factory'];
        yield ['draw.messenger.redirect_to_route_message_handler'];
        yield [RedirectToRouteMessageHandler::class, 'draw.messenger.redirect_to_route_message_handler'];
        yield ['draw.messenger.command.purge_expired_command'];
        yield [PurgeExpiredMessageCommand::class, 'draw.messenger.command.purge_expired_command'];
        yield ['draw.messenger.auto_stamp_envelope_listener'];
        yield [AutoStampEnvelopeListener::class, 'draw.messenger.auto_stamp_envelope_listener'];
        yield ['draw.messenger.message_controller'];
        yield [MessageController::class, 'draw.messenger.message_controller'];
        yield ['draw.messenger.manually_triggered_message_url_generator'];
        yield [ManuallyTriggeredMessageUrlGenerator::class, 'draw.messenger.manually_triggered_message_url_generator'];
        yield ['draw.messenger.envelope_finder'];
        yield [EnvelopeFinder::class, 'draw.messenger.envelope_finder'];
    }

    public function testPrepend(): void
    {
        $containerBuilder = static::getContainerBuilder();

        $containerBuilder->registerExtension($this->getExtension());

        $containerBuilder->loadFromExtension('draw_framework_extra', $this->getConfiguration());

        $this->getExtension()->prepend($containerBuilder);

        $result = $containerBuilder
            ->getExtensionConfig('framework');

        $installationPath = dirname((new ReflectionClass(Broker::class))->getFileName());

        $this->assertSame(
            [
                [
                    'messenger' => [
                        'routing' => [
                            AsyncMessageInterface::class => 'async',
                            AsyncHighPriorityMessageInterface::class => 'async_high_priority',
                            AsyncLowPriorityMessageInterface::class => 'async_low_priority',
                        ],
                    ],
                ],
                [
                    'translator' => [
                        'paths' => [
                            'draw-messenger' => $installationPath.'/Resources/translations',
                        ],
                    ],
                ],
            ],
            $result
        );
    }
}

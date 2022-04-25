<?php

namespace Draw\Bundle\FrameworkExtraBundle\Tests\DependencyInjection;

use Draw\Bundle\FrameworkExtraBundle\DependencyInjection\DrawFrameworkExtraExtension;
use Draw\Component\Messenger\Command\PurgeExpiredMessageCommand;
use Draw\Component\Messenger\Controller\MessageController;
use Draw\Component\Messenger\EnvelopeFinder;
use Draw\Component\Messenger\EventListener\AutoStampEnvelopeListener;
use Draw\Component\Messenger\ManuallyTriggeredMessageUrlGenerator;
use Draw\Component\Messenger\MessageHandler\RedirectToRouteMessageHandler;
use Draw\Component\Messenger\Transport\DrawTransportFactory;
use Draw\Component\Tester\Command\TestsCoverageCheckCommand;
use Draw\Component\Tester\DependencyInjection\ExtensionTestCase;
use Draw\Contracts\Process\ProcessFactoryInterface;
use Symfony\Component\DependencyInjection\Extension\Extension;

class DrawFrameworkExtraExtensionTest extends ExtensionTestCase
{
    public function createExtension(): Extension
    {
        return new DrawFrameworkExtraExtension();
    }

    public function getConfiguration(): array
    {
        return [
            'cron' => [
                'enabled' => false,
            ],
            'console' => [
                'enabled' => false,
            ],
            'security' => [
                'enabled' => false,
            ],
        ];
    }

    public function provideTestHasServiceDefinition(): iterable
    {
        yield ['draw.process.factory'];
        yield [ProcessFactoryInterface::class, 'draw.process.factory'];
        yield ['draw.tester.command.coverage_check'];
        yield [TestsCoverageCheckCommand::class, 'draw.tester.command.coverage_check'];
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
}

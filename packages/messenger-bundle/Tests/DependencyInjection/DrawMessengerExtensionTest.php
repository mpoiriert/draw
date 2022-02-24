<?php

namespace Draw\Bundle\MessengerBundle\Tests\DependencyInjection;

use Draw\Bundle\MessengerBundle\CallToAction\MessageUrlGenerator;
use Draw\Bundle\MessengerBundle\Command\PurgeExpiredMessageCommand;
use Draw\Bundle\MessengerBundle\Controller\MessageController;
use Draw\Bundle\MessengerBundle\DependencyInjection\DrawMessengerExtension;
use Draw\Bundle\MessengerBundle\EventListener\StopWorkerOnSigintSignalListener;
use Draw\Bundle\MessengerBundle\MessageHandler\RedirectToRouteMessageHandler;
use Draw\Component\Messenger\EventListener\AutoStampEnvelopeListener;
use Draw\Component\Messenger\Transport\DrawTransport;
use Draw\Component\Messenger\Transport\DrawTransportFactory;
use Draw\Component\Tester\DependencyInjection\ExtensionTestCase;
use Symfony\Component\DependencyInjection\Extension\Extension;

class DrawMessengerExtensionTest extends ExtensionTestCase
{
    public function createExtension(): Extension
    {
        return new DrawMessengerExtension();
    }

    public function getConfiguration(): array
    {
        return [];
    }

    public function provideTestHasServiceDefinition(): iterable
    {
        yield [DrawTransport::class, 'messenger.transport.draw'];
        yield [MessageUrlGenerator::class];
        yield [PurgeExpiredMessageCommand::class];
        yield [MessageController::class];
        yield [RedirectToRouteMessageHandler::class];
        yield [StopWorkerOnSigintSignalListener::class];
        yield [DrawTransportFactory::class];
        yield [AutoStampEnvelopeListener::class];
    }
}

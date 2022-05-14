<?php

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Draw\Bundle\FrameworkExtraBundle\Controller\Messenger\MessageController;
use Draw\Component\Messenger\Command\PurgeExpiredMessageCommand;
use Draw\Component\Messenger\EnvelopeFinder;
use Draw\Component\Messenger\EventListener\AutoStampEnvelopeListener;
use Draw\Component\Messenger\ManuallyTriggeredMessageUrlGenerator;
use Draw\Component\Messenger\MessageHandler\RedirectToRouteMessageHandler;
use Draw\Component\Messenger\Transport\DrawTransportFactory;
use Psr\Container\ContainerInterface;

return static function (ContainerConfigurator $container) {
    $container
        ->services()

        ->defaults()
            ->autoconfigure()
            ->autowire()
            ->bind(ContainerInterface::class.' $transportLocator', service('messenger.receiver_locator'))
            ->bind('$transportNames', param('draw.messenger.transport_names'))

        ->set('draw.messenger.draw_transport_factory', DrawTransportFactory::class)
        ->arg('$registry', service('doctrine'))
        ->alias(DrawTransportFactory::class, 'draw.messenger.draw_transport_factory')

        ->set('draw.messenger.redirect_to_route_message_handler', RedirectToRouteMessageHandler::class)
        ->alias(RedirectToRouteMessageHandler::class, 'draw.messenger.redirect_to_route_message_handler')

        ->set('draw.messenger.command.purge_expired_command', PurgeExpiredMessageCommand::class)
        ->alias(PurgeExpiredMessageCommand::class, 'draw.messenger.command.purge_expired_command')

        ->set('draw.messenger.auto_stamp_envelope_listener', AutoStampEnvelopeListener::class)
        ->alias(AutoStampEnvelopeListener::class, 'draw.messenger.auto_stamp_envelope_listener')

        ->set('draw.messenger.manually_triggered_message_url_generator', ManuallyTriggeredMessageUrlGenerator::class)
        ->alias(ManuallyTriggeredMessageUrlGenerator::class, 'draw.messenger.manually_triggered_message_url_generator')

        ->set(MessageController::class, MessageController::class)
        ->tag('controller.service_arguments')

        ->set('draw.messenger.envelope_finder', EnvelopeFinder::class)
        ->alias(EnvelopeFinder::class, 'draw.messenger.envelope_finder');
};

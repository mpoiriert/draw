<?php

namespace Draw\Bundle\SonataExtraBundle\ActionableAdmin;

use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\Log\LoggerInterface;
use Sonata\AdminBundle\Admin\AdminInterface;
use Symfony\Component\DependencyInjection\Attribute\Autoconfigure;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

#[
    Autoconfigure(shared: false),
    AutoconfigureTag(
        'monolog.logger',
        attributes: [
            'channel' => 'sonata_admin',
        ]
    ),
    AutoconfigureTag(
        'logger.decorate',
        attributes: [
            'message' => '[ObjectActionExecutioner] {message}',
        ]
    )
]
class ObjectActionExecutionerFactory
{
    public function __construct(
        private EventDispatcherInterface $eventDispatcher,
        private ?LoggerInterface $logger
    ) {
    }

    public function create(
        AdminInterface $admin,
        string $action,
        object $target,
    ): ObjectActionExecutioner {
        return new ObjectActionExecutioner(
            $admin,
            $action,
            $target,
            $this->eventDispatcher,
            $this->logger,
        );
    }
}

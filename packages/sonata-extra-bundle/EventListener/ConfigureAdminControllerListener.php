<?php

declare(strict_types=1);

namespace Draw\Bundle\SonataExtraBundle\EventListener;

use Draw\Bundle\SonataExtraBundle\Controller\AdminControllerInterface;
use Sonata\AdminBundle\Request\AdminFetcherInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * @internal
 */
final class ConfigureAdminControllerListener implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::CONTROLLER => 'onKernelController',
        ];
    }

    public function __construct(private AdminFetcherInterface $adminFetcher)
    {
    }

    public function onKernelController(ControllerEvent $event): void
    {
        $controller = $event->getController();

        if (\is_array($controller)) {
            $controller = $controller[0];
        }

        if (!$controller instanceof AdminControllerInterface) {
            return;
        }

        $controller->configureAdmin($this->adminFetcher->get($event->getRequest()));
    }
}

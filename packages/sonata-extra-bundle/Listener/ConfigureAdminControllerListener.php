<?php

declare(strict_types=1);

namespace Draw\Bundle\SonataExtraBundle\Listener;

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
    private AdminFetcherInterface $adminFetcher;

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::CONTROLLER => 'onKernelController',
        ];
    }

    public function __construct(AdminFetcherInterface $adminFetcher)
    {
        $this->adminFetcher = $adminFetcher;
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

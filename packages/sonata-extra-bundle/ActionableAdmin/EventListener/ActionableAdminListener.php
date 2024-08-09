<?php

namespace Draw\Bundle\SonataExtraBundle\ActionableAdmin\EventListener;

use Draw\Bundle\SonataExtraBundle\ActionableAdmin\ActionableAdminInterface;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpKernel\Event\ControllerArgumentsEvent;

class ActionableAdminListener
{
    #[AsEventListener(priority: -1000)]
    public function onControllerEvent(ControllerArgumentsEvent $event): void
    {
        $admin = null;
        foreach ($event->getArguments() as $argument) {
            if ($argument instanceof ActionableAdminInterface) {
                $admin = $argument;

                break;
            }
        }

        if (!$admin instanceof ActionableAdminInterface) {
            return;
        }

        $request = $event->getRequest();

        $objectId = $request->get($admin->getIdParameter());

        $object = $admin->getObject($objectId);

        $action = $request->attributes->get('_actionableAdmin')['action'] ?? null;

        if (null === $action) {
            return;
        }

        $admin->checkAccess($action, $object);
    }
}

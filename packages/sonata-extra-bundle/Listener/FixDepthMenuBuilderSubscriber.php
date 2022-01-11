<?php

namespace Draw\Bundle\SonataExtraBundle\Listener;

use Sonata\AdminBundle\Event\ConfigureMenuEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class FixDepthMenuBuilderSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return [
            ConfigureMenuEvent::SIDEBAR => [
                ['fixDepth', -255],
            ],
        ];
    }

    public function fixDepth(ConfigureMenuEvent $event)
    {
        $menu = $event->getMenu();

        foreach ($menu->getChildren() as $groupMenu) {
            if (1 !== count($groupMenu->getChildren())) {
                continue;
            }
            $subMenu = current($groupMenu->getChildren());
            $groupMenu->setUri($subMenu->getUri());
            $groupMenu->removeChild($subMenu);
        }
    }
}

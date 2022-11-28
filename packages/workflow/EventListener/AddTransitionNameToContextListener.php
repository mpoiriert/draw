<?php

namespace Draw\Component\Workflow\EventListener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Workflow\Event\TransitionEvent;

class AddTransitionNameToContextListener implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [
            'workflow.transition' => 'addTransitionToContext',
        ];
    }

    public function addTransitionToContext(TransitionEvent $transitionEvent): void
    {
        $transitionEvent->setContext(array_merge(
            $transitionEvent->getContext(),
            ['_transitionName' => $transitionEvent->getTransition()->getName()]
        ));
    }
}

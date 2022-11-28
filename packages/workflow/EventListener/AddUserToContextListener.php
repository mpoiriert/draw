<?php

namespace Draw\Component\Workflow\EventListener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Workflow\Event\TransitionEvent;

class AddUserToContextListener implements EventSubscriberInterface
{
    private Security $security;

    public static function getSubscribedEvents(): array
    {
        return [
            'workflow.transition' => 'addUserToContext',
        ];
    }

    public function __construct(Security $security)
    {
        $this->security = $security;
    }

    public function addUserToContext(TransitionEvent $transitionEvent): void
    {
        $user = $this->security->getUser();
        if (null === $user) {
            return;
        }

        $transitionEvent->setContext(array_merge(
            $transitionEvent->getContext(),
            ['_user' => $user]
        ));
    }
}

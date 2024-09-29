<?php

namespace Draw\Bundle\SonataExtraBundle\ActionableAdmin\EventListener;

use Draw\Bundle\SonataExtraBundle\ActionableAdmin\Event\ExecutionEvent;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

class CheckAccessEventListener
{
    public const CHECK_ACCESS = 'check_access.check';

    public function __construct(
        private bool $defaultCheckAccess = true,
    ) {
    }

    #[AsEventListener(priority: 1000)]
    public function onExecutionEvent(ExecutionEvent $event): void
    {
        $objectActionExecutioner = $event->getObjectActionExecutioner();

        if (!($objectActionExecutioner->options[self::CHECK_ACCESS] ?? $this->defaultCheckAccess)) {
            return;
        }

        $object = $event->getObject();

        if ($objectActionExecutioner->getAdmin()->hasAccess($objectActionExecutioner->getAction(), $object)) {
            return;
        }

        $event->skip('insufficient-access');
    }
}

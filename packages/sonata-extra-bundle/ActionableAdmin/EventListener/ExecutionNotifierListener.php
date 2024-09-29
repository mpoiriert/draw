<?php

namespace Draw\Bundle\SonataExtraBundle\ActionableAdmin\EventListener;

use Draw\Bundle\SonataExtraBundle\ActionableAdmin\Event\ExecutionErrorEvent;
use Draw\Bundle\SonataExtraBundle\ActionableAdmin\Event\PostExecutionEvent;
use Draw\Bundle\SonataExtraBundle\ActionableAdmin\ExecutionNotifier;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

class ExecutionNotifierListener
{
    public const AUTO_NOTIFY = 'notifier.autoNotify';

    public const NOTIFY_ERROR = 'notifier.notifyError';

    public function __construct(
        private ExecutionNotifier $executionNotifier,
        private bool $autoNotify = true,
        private bool $notifyError = true,
    ) {
    }

    #[AsEventListener]
    public function onPostExecutionEvent(PostExecutionEvent $event): void
    {
        $notify = $event->getObjectActionExecutioner()->options[self::AUTO_NOTIFY] ?? $this->autoNotify;

        if ($notify) {
            $this->executionNotifier->notifyExecution($event->getObjectActionExecutioner());
        }
    }

    #[AsEventListener]
    public function onExecutionErrorEvent(ExecutionErrorEvent $event): void
    {
        $notify = $event->getObjectActionExecutioner()->options[self::NOTIFY_ERROR] ?? $this->notifyError;

        if ($notify) {
            $this->executionNotifier->notifyError(
                $event->getObjectActionExecutioner(),
                $event->getError(),
                $event->getObject()
            );
        }
    }
}

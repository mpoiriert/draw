<?php

declare(strict_types=1);

namespace Draw\Component\CronJob\MessageHandler;

use Draw\Component\CronJob\Event\PostCronJobExecutionEvent;
use Draw\Component\CronJob\Event\PreCronJobExecutionEvent;
use Draw\Component\CronJob\Message\ExecuteCronJobMessage;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class ExecuteCronJobMessageHandler
{
    public function __construct(
        private EventDispatcherInterface $eventDispatcher,
    ) {
    }

    #[AsMessageHandler]
    public function handleExecuteCronJobMessage(ExecuteCronJobMessage $message): void
    {
        $executionCancelled = $this->eventDispatcher
            ->dispatch(new PreCronJobExecutionEvent($execution = $message->getExecution()))
            ->isExecutionCancelled();

        if ($executionCancelled) {
            return;
        }

        // @TODO

        $this->eventDispatcher->dispatch(new PostCronJobExecutionEvent($execution));
    }
}

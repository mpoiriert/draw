<?php

declare(strict_types=1);

namespace Draw\Component\CronJob\MessageHandler;

use Draw\Component\CronJob\CronJobProcessor;
use Draw\Component\CronJob\Message\ExecuteCronJobMessage;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

class ExecuteCronJobMessageHandler
{
    public function __construct(
        private CronJobProcessor $cronJobProcessor,
    ) {
    }

    #[AsMessageHandler]
    public function handleExecuteCronJobMessage(ExecuteCronJobMessage $message): void
    {
        $this->cronJobProcessor->process($message->getExecution());
    }
}

<?php

declare(strict_types=1);

namespace Draw\Component\CronJob\MessageHandler;

use Doctrine\Persistence\ManagerRegistry;
use Draw\Component\CronJob\CronJobProcessor;
use Draw\Component\CronJob\Entity\CronJobExecution;
use Draw\Component\CronJob\Message\ExecuteCronJobMessage;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

class ExecuteCronJobMessageHandler
{
    public function __construct(
        private ManagerRegistry $managerRegistry,
        private CronJobProcessor $cronJobProcessor,
    ) {
    }

    #[AsMessageHandler]
    public function handleExecuteCronJobMessage(ExecuteCronJobMessage $message): void
    {
        if (!($execution = $message->getExecution())->isExecutable(new \DateTimeImmutable())) {
            $execution->skip();

            $this->managerRegistry
                ->getManagerForClass(CronJobExecution::class)
                ->flush();

            return;
        }

        $this->cronJobProcessor->process($execution);
    }
}

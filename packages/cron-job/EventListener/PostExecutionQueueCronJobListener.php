<?php

namespace Draw\Component\CronJob\EventListener;

use Doctrine\Persistence\ManagerRegistry;
use Draw\Component\CronJob\CronJobProcessor;
use Draw\Component\CronJob\Entity\CronJob;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Event\ConsoleTerminateEvent;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

/**
 * This command listener allow to queue a cron job by name after the execution if it's a success.
 *
 * Example:
 *   console/bin acme:purge-database --draw-draw-post-execution-queue-cron-job
 */
class PostExecutionQueueCronJobListener
{
    final public const OPTION_POST_EXECUTION_QUEUE_CRON_JOB = 'draw-post-execution-queue-cron-job';

    public function __construct(
        private CronJobProcessor $cronJobProcessor,
        private ManagerRegistry $managerRegistry,
        private ?LoggerInterface $logger = new NullLogger(),
    ) {
    }

    #[AsEventListener(priority: -1000)]
    public function triggerCronJob(ConsoleTerminateEvent $event): void
    {
        if (Command::SUCCESS !== $event->getExitCode()) {
            return;
        }

        $input = $event->getInput();

        if (!$input->hasOption(static::OPTION_POST_EXECUTION_QUEUE_CRON_JOB)) {
            return;
        }

        $cronJobRepository = $this->managerRegistry->getRepository(CronJob::class);

        $cronJobNames = $input->getOption(static::OPTION_POST_EXECUTION_QUEUE_CRON_JOB);

        foreach ($cronJobNames as $cronJobName) {
            $cronJob = $cronJobRepository
                ->findOneBy(['name' => $cronJobName]);

            if (null === $cronJob) {
                $this->logger->error(\sprintf('Cron job "%s" could not be found.', $cronJobName));

                continue;
            }

            $this->logger->info(\sprintf('Queueing cron job "%s"...', $cronJob->getName()));

            $this->cronJobProcessor->queue($cronJob, true);
        }
    }
}

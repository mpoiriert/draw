<?php

namespace Draw\Component\Messenger\Versioning\EventListener;

use Draw\Component\Messenger\Broker\Event\BrokerRunningEvent;
use Draw\Contracts\Application\VersionVerificationInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Messenger\Event\WorkerRunningEvent;
use Symfony\Component\Messenger\Event\WorkerStartedEvent;
use Symfony\Component\Messenger\Worker;

class StopOnNewVersionListener implements EventSubscriberInterface
{
    private ?LoggerInterface $logger;

    private VersionVerificationInterface $versionVerification;

    public static function getSubscribedEvents(): array
    {
        return [
            WorkerStartedEvent::class => 'onWorkerStarted',
            WorkerRunningEvent::class => 'onWorkerRunning',
            BrokerRunningEvent::class => 'onBrokerRunningEvent',
        ];
    }

    public function __construct(VersionVerificationInterface $versionVerification, ?LoggerInterface $logger = null)
    {
        $this->versionVerification = $versionVerification;
        $this->logger = $logger;
    }

    public function onWorkerStarted(WorkerStartedEvent $event): void
    {
        $this->stopWorkerIfNeeded($event->getWorker());
    }

    public function onWorkerRunning(WorkerRunningEvent $event): void
    {
        $this->stopWorkerIfNeeded($event->getWorker());
    }

    public function onBrokerRunningEvent(BrokerRunningEvent $event): void
    {
        if ($this->applicationVersionIsSync()) {
            return;
        }

        $event->getBroker()->stop();

        if (null !== $this->logger) {
            $this->logger->info(
                'Broker stopped due to version out of sync. Running version {runningVersion}, deployed version {deployedVersion}',
                [
                    'deployedVersion' => $this->versionVerification->getDeployedVersion(),
                    'runningVersion' => $this->versionVerification->getRunningVersion(),
                ]
            );
        }
    }

    private function applicationVersionIsSync(): bool
    {
        if (null === $this->versionVerification->getRunningVersion()) {
            return true;
        }

        if ($this->versionVerification->isUpToDate()) {
            return true;
        }

        return false;
    }

    private function stopWorkerIfNeeded(Worker $worker): void
    {
        if ($this->applicationVersionIsSync()) {
            return;
        }

        $worker->stop();

        if (null !== $this->logger) {
            $this->logger->info(
                'Worker stopped due to version out of sync. Running version {runningVersion}, deployed version {deployedVersion}',
                [
                    'deployedVersion' => $this->versionVerification->getDeployedVersion(),
                    'runningVersion' => $this->versionVerification->getRunningVersion(),
                ]
            );
        }
    }
}

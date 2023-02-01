<?php

namespace Draw\Component\Messenger\Tests\EventListener;

use ColinODell\PsrTestLogger\TestLogger;
use Draw\Component\Messenger\Broker\Broker;
use Draw\Component\Messenger\Broker\Event\BrokerRunningEvent;
use Draw\Component\Messenger\Versioning\EventListener\StopOnNewVersionListener;
use Draw\Contracts\Application\VersionVerificationInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Messenger\Event\WorkerRunningEvent;
use Symfony\Component\Messenger\Event\WorkerStartedEvent;
use Symfony\Component\Messenger\Worker;

/**
 * @covers \Draw\Component\Messenger\Versioning\EventListener\StopOnNewVersionListener
 */
class StopOnNewVersionListenerTest extends TestCase implements VersionVerificationInterface
{
    private StopOnNewVersionListener $service;

    private TestLogger $logger;

    private ?string $runningVersion = null;
    private ?string $deployedVersion = null;
    private bool $isUpToDate = true;

    protected function setUp(): void
    {
        $this->service = new StopOnNewVersionListener(
            $this,
            $this->logger = new TestLogger()
        );
    }

    public function getRunningVersion(): ?string
    {
        return $this->runningVersion;
    }

    public function getDeployedVersion(): ?string
    {
        return $this->deployedVersion;
    }

    public function isUpToDate(): bool
    {
        return $this->isUpToDate;
    }

    public function testConstruct(): void
    {
        static::assertInstanceOf(EventSubscriberInterface::class, $this->service);
    }

    public function testGetSubscribedEvents(): void
    {
        static::assertSame(
            [
                WorkerStartedEvent::class => 'onWorkerStarted',
                WorkerRunningEvent::class => 'onWorkerRunning',
                BrokerRunningEvent::class => 'onBrokerRunningEvent',
            ],
            $this->service::getSubscribedEvents()
        );
    }

    public function testOnWorkerStarted(): void
    {
        $this->runningVersion = '1.0.0';
        $this->isUpToDate = false;

        $worker = $this->createMock(Worker::class);
        $worker->expects(static::once())->method('stop');

        $this->service->onWorkerStarted(new WorkerStartedEvent($worker));
    }

    public function testOnWorkerStartedUpToDate(): void
    {
        $this->runningVersion = '1.0.0';
        $this->isUpToDate = true;

        $worker = $this->createMock(Worker::class);
        $worker->expects(static::never())->method('stop');

        $this->service->onWorkerStarted(new WorkerStartedEvent($worker));
    }

    public function testOnWorkerRunning(): void
    {
        $this->runningVersion = '1.0.0';
        $this->isUpToDate = false;

        $worker = $this->createMock(Worker::class);
        $worker->expects(static::once())->method('stop');

        $this->service->onWorkerRunning(new WorkerRunningEvent($worker, false));

        $this->logger->hasInfo([
            'message' => 'Worker stopped due to version out of sync. Running version {runningVersion}, deployed version {deployedVersion}',
            'context' => [
                'deployedVersion' => $this->deployedVersion,
                'runningVersion' => $this->runningVersion,
            ],
        ]);
    }

    public function testOnWorkerRunningUpToDate(): void
    {
        $this->runningVersion = '1.0.0';
        $this->isUpToDate = true;

        $worker = $this->createMock(Worker::class);
        $worker->expects(static::never())->method('stop');

        $this->service->onWorkerRunning(new WorkerRunningEvent($worker, false));
    }

    public function testOnWorkerRunningUpToDateRunningVersionIsNull(): void
    {
        $this->runningVersion = null;

        $worker = $this->createMock(Worker::class);
        $worker->expects(static::never())->method('stop');

        $this->service->onWorkerRunning(new WorkerRunningEvent($worker, false));
    }

    public function testOnBrokerRunningEvent(): void
    {
        $this->runningVersion = '1.0.0';
        $this->isUpToDate = false;

        $broker = $this->createMock(Broker::class);
        $broker->expects(static::once())->method('stop');

        $this->service->onBrokerRunningEvent(new BrokerRunningEvent($broker));

        $this->logger->hasInfo([
            'message' => 'Broker stopped due to version out of sync. Running version {runningVersion}, deployed version {deployedVersion}',
            'context' => [
                'deployedVersion' => $this->deployedVersion,
                'runningVersion' => $this->runningVersion,
            ],
        ]);
    }

    public function testOnBrokerRunningEventUpToDate(): void
    {
        $this->runningVersion = '1.0.0';
        $this->isUpToDate = true;

        $broker = $this->createMock(Broker::class);
        $broker->expects(static::never())->method('stop');

        $this->service->onBrokerRunningEvent(new BrokerRunningEvent($broker));
    }
}

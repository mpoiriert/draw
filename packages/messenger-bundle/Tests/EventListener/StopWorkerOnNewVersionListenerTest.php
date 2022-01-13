<?php

namespace Draw\Bundle\MessengerBundle\Tests\EventListener;

use Draw\Bundle\MessengerBundle\Broker\Broker;
use Draw\Bundle\MessengerBundle\Broker\Event\BrokerRunningEvent;
use Draw\Bundle\MessengerBundle\EventListener\StopWorkerOnNewVersionListener;
use Draw\Contracts\Application\VersionVerificationInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Messenger\Event\WorkerRunningEvent;
use Symfony\Component\Messenger\Event\WorkerStartedEvent;
use Symfony\Component\Messenger\Worker;

class StopWorkerOnNewVersionListenerTest extends TestCase implements VersionVerificationInterface
{
    private $stopWorkerOnNewVersionListener;

    private $runningVersion = null;
    private $deployedVersion = null;
    private $isUpToData = true;

    public function setUp(): void
    {
        $this->stopWorkerOnNewVersionListener = new StopWorkerOnNewVersionListener($this);
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
        return $this->isUpToData;
    }

    public function testConstruct(): void
    {
        $this->assertInstanceOf(EventSubscriberInterface::class, $this->stopWorkerOnNewVersionListener);
    }

    public function testGetSubscribedEvents(): void
    {
        $this->assertSame(
            [
                WorkerStartedEvent::class => 'onWorkerStarted',
                WorkerRunningEvent::class => 'onWorkerRunning',
                BrokerRunningEvent::class => 'onBrokerRunningEvent',
            ],
            $this->stopWorkerOnNewVersionListener::getSubscribedEvents()
        );
    }

    public function testOnWorkerStarted(): void
    {
        $this->runningVersion = '1.0.0';
        $this->isUpToData = false;

        $worker = $this->createMock(Worker::class);
        $worker->expects($this->once())->method('stop');

        $this->stopWorkerOnNewVersionListener->onWorkerStarted(new WorkerStartedEvent($worker));
    }

    public function testOnWorkerStartedUpToDate(): void
    {
        $this->runningVersion = '1.0.0';
        $this->isUpToData = true;

        $worker = $this->createMock(Worker::class);
        $worker->expects($this->never())->method('stop');

        $this->stopWorkerOnNewVersionListener->onWorkerStarted(new WorkerStartedEvent($worker));
    }

    public function testOnWorkerRunning(): void
    {
        $this->runningVersion = '1.0.0';
        $this->isUpToData = false;

        $worker = $this->createMock(Worker::class);
        $worker->expects($this->once())->method('stop');

        $this->stopWorkerOnNewVersionListener->onWorkerRunning(new WorkerRunningEvent($worker, false));
    }

    public function testOnWorkerRunningUpToDate(): void
    {
        $this->runningVersion = '1.0.0';
        $this->isUpToData = true;

        $worker = $this->createMock(Worker::class);
        $worker->expects($this->never())->method('stop');

        $this->stopWorkerOnNewVersionListener->onWorkerRunning(new WorkerRunningEvent($worker, false));
    }

    public function testOnBrokerRunningEvent(): void
    {
        $this->runningVersion = '1.0.0';
        $this->isUpToData = false;

        $broker = $this->createMock(Broker::class);
        $broker->expects($this->once())->method('stop');

        $this->stopWorkerOnNewVersionListener->onBrokerRunningEvent(new BrokerRunningEvent($broker));
    }

    public function testOnBrokerRunningEventUpToDate(): void
    {
        $this->runningVersion = '1.0.0';
        $this->isUpToData = true;

        $broker = $this->createMock(Broker::class);
        $broker->expects($this->never())->method('stop');

        $this->stopWorkerOnNewVersionListener->onBrokerRunningEvent(new BrokerRunningEvent($broker));
    }
}

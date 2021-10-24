<?php

namespace Draw\Component\Messenger\Tests\EventListener;

use Draw\Component\Messenger\EventListener\StopWorkerOnFailureLimitListener;
use Exception;
use PHPUnit\Framework\TestCase;
use stdClass;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Event\WorkerMessageFailedEvent;
use Symfony\Component\Messenger\Event\WorkerRunningEvent;
use Symfony\Component\Messenger\Worker;

class StopWorkerOnFailureLimitListenerTest extends TestCase
{
    private $stopWorkerOnFailureLimitListener = null;

    public function setUp(): void
    {
        $this->stopWorkerOnFailureLimitListener = new StopWorkerOnFailureLimitListener();
    }

    public function testOnWorkerRunning(): void
    {
        $this->stopWorkerOnFailureLimitListener->onMessageFailed(
            new WorkerMessageFailedEvent(
                new Envelope(new stdClass()),
                'test',
                new Exception()
            )
        );

        $workerMock = $this->createMock(Worker::class);

        $workerMock->expects($this->once())->method('stop');

        $this->stopWorkerOnFailureLimitListener->onWorkerRunning(new WorkerRunningEvent(
            $workerMock,
            false
        ));
    }
}

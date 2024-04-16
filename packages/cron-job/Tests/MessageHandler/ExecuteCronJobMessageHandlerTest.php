<?php

declare(strict_types=1);

namespace Draw\Component\CronJob\Tests\MessageHandler;

use Draw\Component\CronJob\CronJobProcessor;
use Draw\Component\CronJob\Entity\CronJobExecution;
use Draw\Component\CronJob\Event\PostCronJobExecutionEvent;
use Draw\Component\CronJob\Event\PreCronJobExecutionEvent;
use Draw\Component\CronJob\Message\ExecuteCronJobMessage;
use Draw\Component\CronJob\MessageHandler\ExecuteCronJobMessageHandler;
use Draw\Component\Tester\MockTrait;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class ExecuteCronJobMessageHandlerTest extends TestCase
{
    use MockTrait;

    private ExecuteCronJobMessageHandler $handler;

    private EventDispatcherInterface&MockObject $eventDispatcher;

    private CronJobProcessor&MockObject $cronJobProcessor;

    protected function setUp(): void
    {
        parent::setUp();

        $this->handler = new ExecuteCronJobMessageHandler(
            $this->eventDispatcher = $this->createMock(EventDispatcherInterface::class),
            $this->cronJobProcessor = $this->createMock(CronJobProcessor::class)
        );
    }

    public function testHandleExecuteCronJobMessage(): void
    {
        $this->eventDispatcher
            ->expects(static::exactly(2))
            ->method('dispatch')
            ->with(
                ...static::withConsecutive(
                    [
                        $preExecutionEvent = new PreCronJobExecutionEvent(
                            $execution = new CronJobExecution()
                        ),
                    ],
                    [
                        $postExecutionEvent = new PostCronJobExecutionEvent(
                            $execution
                        ),
                    ]
                )
            )
            ->willReturnOnConsecutiveCalls($preExecutionEvent, $postExecutionEvent);

        $this->cronJobProcessor
            ->expects(static::once())
            ->method('process')
            ->with($execution);

        $this->handler->handleExecuteCronJobMessage(new ExecuteCronJobMessage($execution));
    }

    public function testHandleExecuteCronJobMessageWithCancelledExecution(): void
    {
        $this->eventDispatcher
            ->expects(static::once())
            ->method('dispatch')
            ->with(
                new PreCronJobExecutionEvent($execution = new CronJobExecution())
            )
            ->willReturn(
                new PreCronJobExecutionEvent($execution, true)
            );

        $this->cronJobProcessor
            ->expects(static::never())
            ->method('process');

        $this->handler->handleExecuteCronJobMessage(new ExecuteCronJobMessage($execution));
    }
}

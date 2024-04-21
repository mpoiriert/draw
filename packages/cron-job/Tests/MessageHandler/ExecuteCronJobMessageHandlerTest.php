<?php

declare(strict_types=1);

namespace Draw\Component\CronJob\Tests\MessageHandler;

use Draw\Component\CronJob\CronJobProcessor;
use Draw\Component\CronJob\Entity\CronJobExecution;
use Draw\Component\CronJob\Message\ExecuteCronJobMessage;
use Draw\Component\CronJob\MessageHandler\ExecuteCronJobMessageHandler;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

#[CoversClass(ExecuteCronJobMessageHandler::class)]
class ExecuteCronJobMessageHandlerTest extends TestCase
{
    private ExecuteCronJobMessageHandler $handler;

    private CronJobProcessor&MockObject $cronJobProcessor;

    protected function setUp(): void
    {
        parent::setUp();

        $this->handler = new ExecuteCronJobMessageHandler(
            $this->cronJobProcessor = $this->createMock(CronJobProcessor::class)
        );
    }

    public function testHandleExecuteCronJobMessage(): void
    {
        $this->cronJobProcessor
            ->expects(static::once())
            ->method('process')
            ->with($execution = $this->createCronJobExecution());

        $this->handler->handleExecuteCronJobMessage(
            new ExecuteCronJobMessage($execution)
        );
    }

    public function testHandleExecuteCronJobMessageWithNotExecutableExecution(): void
    {
        $this->cronJobProcessor
            ->expects(static::never())
            ->method('process');

        $this->handler->handleExecuteCronJobMessage(
            new ExecuteCronJobMessage($this->createCronJobExecution(false))
        );
    }

    private function createCronJobExecution(bool $executable = true): CronJobExecution&MockObject
    {
        $execution = $this->createMock(CronJobExecution::class);
        $execution
            ->expects(static::any())
            ->method('isExecutable')
            ->willReturn($executable);

        return $execution;
    }
}

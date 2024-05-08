<?php

declare(strict_types=1);

namespace Draw\Component\Messenger\Tests\MessageHandler;

use Draw\Component\Messenger\Message\RetryFailedMessageMessage;
use Draw\Component\Messenger\MessageHandler\RetryFailedMessageMessageHandler;
use Draw\Contracts\Process\ProcessFactoryInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Process\Process;

#[
    CoversClass(RetryFailedMessageMessage::class),
    CoversClass(RetryFailedMessageMessageHandler::class),
]
class RetryFailedMessageMessageHandlerTest extends TestCase
{
    private const CONSOLE_PATH = 'bin/console';

    private RetryFailedMessageMessageHandler $handler;

    private ProcessFactoryInterface&MockObject $processFactory;

    protected function setUp(): void
    {
        parent::setUp();

        $this->handler = new RetryFailedMessageMessageHandler(
            $this->processFactory = $this->createMock(ProcessFactoryInterface::class),
            self::CONSOLE_PATH
        );
    }

    public function testHandleRetryFailedMessageMessage(): void
    {
        $this->processFactory
            ->expects(static::once())
            ->method('create')
            ->with(
                [
                    self::CONSOLE_PATH,
                    'messenger:failed:retry',
                    $messageId = Uuid::uuid6()->toString(),
                    '--force',
                ]
            )
            ->willReturn($process = $this->createMock(Process::class));

        $process
            ->expects(static::once())
            ->method('mustRun');

        $this->handler->handleRetryFailedMessageMessage(
            new RetryFailedMessageMessage($messageId)
        );
    }
}

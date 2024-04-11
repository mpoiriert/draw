<?php

declare(strict_types=1);

namespace Draw\Component\CronJob\Tests\MessageHandler;

use Draw\Bundle\TesterBundle\PHPUnit\Extension\SetUpAutowire\AutowiredCompletionAwareInterface;
use Draw\Bundle\TesterBundle\PHPUnit\Extension\SetUpAutowire\AutowireMock;
use Draw\Bundle\TesterBundle\WebTestCase;
use Draw\Component\CronJob\Entity\CronJobExecution;
use Draw\Component\CronJob\Event\PostCronJobExecutionEvent;
use Draw\Component\CronJob\Event\PreCronJobExecutionEvent;
use Draw\Component\CronJob\Message\ExecuteCronJobMessage;
use Draw\Component\CronJob\MessageHandler\ExecuteCronJobMessageHandler;
use Draw\Component\Tester\MockTrait;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class ExecuteCronJobMessageHandlerTest extends WebTestCase implements AutowiredCompletionAwareInterface
{
    use MockTrait;

    #[AutowireMock]
    private EventDispatcherInterface&MockObject $eventDispatcher;

    private ExecuteCronJobMessageHandler $handler;

    public function postAutowire(): void
    {
        $this->handler = new ExecuteCronJobMessageHandler(
            $this->eventDispatcher
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

        $this->handler->handleExecuteCronJobMessage(new ExecuteCronJobMessage($execution));
    }
}

<?php

namespace Draw\Component\Console\Tests\Event;

use Draw\Component\Console\Event\CommandErrorEvent;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Symfony\Contracts\EventDispatcher\Event;

/**
 * @internal
 */
#[CoversClass(CommandErrorEvent::class)]
class CommandErrorEventTest extends TestCase
{
    private CommandErrorEvent $event;

    private string $executionId;

    private string $outputString;

    protected function setUp(): void
    {
        $this->event = new CommandErrorEvent(
            $this->executionId = uniqid('id-'),
            $this->outputString = uniqid('output-')
        );
    }

    public function testConstruct(): void
    {
        static::assertInstanceOf(
            Event::class,
            $this->event
        );
    }

    public function testGetExecutionId(): void
    {
        static::assertSame(
            $this->executionId,
            $this->event->getExecutionId()
        );
    }

    public function testGetOutputString(): void
    {
        static::assertSame(
            $this->outputString,
            $this->event->getOutputString()
        );
    }

    public function testAcknowledge(): void
    {
        static::assertNull($this->event->getAutoAcknowledgeReason());

        static::assertFalse($this->event->isAutoAcknowledge());

        static::assertSame(
            $this->event,
            $this->event->acknowledge($reason = uniqid('reason-'))
        );

        static::assertSame(
            $reason,
            $this->event->getAutoAcknowledgeReason()
        );

        static::assertTrue($this->event->isAutoAcknowledge());

        static::assertTrue($this->event->isPropagationStopped());
    }
}

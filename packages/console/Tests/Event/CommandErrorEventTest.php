<?php

namespace Draw\Component\Console\Tests\Event;

use Draw\Component\Console\Event\CommandErrorEvent;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

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

    public function testGetExecutionId(): void
    {
        $this->assertSame(
            $this->executionId,
            $this->event->getExecutionId()
        );
    }

    public function testGetOutputString(): void
    {
        $this->assertSame(
            $this->outputString,
            $this->event->getOutputString()
        );
    }

    public function testAcknowledge(): void
    {
        $this->assertNull($this->event->getAutoAcknowledgeReason());

        $this->assertFalse($this->event->isAutoAcknowledge());

        $this->assertSame(
            $this->event,
            $this->event->acknowledge($reason = uniqid('reason-'))
        );

        $this->assertSame(
            $reason,
            $this->event->getAutoAcknowledgeReason()
        );

        $this->assertTrue($this->event->isAutoAcknowledge());

        $this->assertTrue($this->event->isPropagationStopped());
    }
}

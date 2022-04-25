<?php

namespace Draw\Component\Console\Tests\Event;

use Draw\Component\Console\Event\CommandErrorEvent;
use PHPUnit\Framework\TestCase;
use Symfony\Contracts\EventDispatcher\Event;

/**
 * @covers \Draw\Component\Console\Event\CommandErrorEvent
 */
class CommandErrorEventTest extends TestCase
{
    private CommandErrorEvent $event;

    private int $executionId;

    private string $outputString;

    public function setUp(): void
    {
        $this->event = new CommandErrorEvent(
            $this->executionId = rand(PHP_INT_MIN, PHP_INT_MAX),
            $this->outputString = uniqid('output-')
        );
    }

    public function testConstruct(): void
    {
        $this->assertInstanceOf(
            Event::class,
            $this->event
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

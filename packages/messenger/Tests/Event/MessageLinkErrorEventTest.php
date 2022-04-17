<?php

namespace Draw\Component\Messenger\Tests\Event;

use Draw\Component\Messenger\Event\MessageLinkErrorEvent;
use Exception;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\EventDispatcher\Event;

/**
 * @covers \Draw\Component\Messenger\Event\MessageLinkErrorEvent
 */
class MessageLinkErrorEventTest extends TestCase
{
    private MessageLinkErrorEvent $event;

    private Request $request;

    private string $messageId;

    private Exception $error;

    public function setUp(): void
    {
        $this->event = new MessageLinkErrorEvent(
            $this->request = new Request(),
            $this->messageId = uniqid('message-id-'),
            $this->error = new Exception()
        );
    }

    public function testConstruct(): void
    {
        $this->assertInstanceOf(
            Event::class,
            $this->event
        );
    }

    public function testGetRequest(): void
    {
        $this->assertSame(
            $this->request,
            $this->event->getRequest()
        );
    }

    public function testGetMessageId(): void
    {
        $this->assertSame(
            $this->messageId,
            $this->event->getMessageId()
        );
    }

    public function testGetError(): void
    {
        $this->assertSame(
            $this->error,
            $this->event->getError()
        );
    }

    public function testResponseMutator(): void
    {
        $this->assertFalse($this->event->isPropagationStopped());

        $this->assertNull($this->event->getResponse());

        $this->assertSame(
            $this->event,
            $this->event->setResponse($value = new Response())
        );

        $this->assertSame(
            $value,
            $this->event->getResponse()
        );

        $this->assertTrue($this->event->isPropagationStopped());
    }
}

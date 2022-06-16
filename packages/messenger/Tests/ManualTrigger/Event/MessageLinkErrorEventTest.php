<?php

namespace Draw\Component\Messenger\Tests\ManualTrigger\Event;

use Draw\Component\Messenger\ManualTrigger\Event\MessageLinkErrorEvent;
use Exception;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\EventDispatcher\Event;

/**
 * @covers \Draw\Component\Messenger\ManualTrigger\Event\MessageLinkErrorEvent
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
        static::assertInstanceOf(
            Event::class,
            $this->event
        );
    }

    public function testGetRequest(): void
    {
        static::assertSame(
            $this->request,
            $this->event->getRequest()
        );
    }

    public function testGetMessageId(): void
    {
        static::assertSame(
            $this->messageId,
            $this->event->getMessageId()
        );
    }

    public function testGetError(): void
    {
        static::assertSame(
            $this->error,
            $this->event->getError()
        );
    }

    public function testResponseMutator(): void
    {
        static::assertFalse($this->event->isPropagationStopped());

        static::assertNull($this->event->getResponse());

        static::assertSame(
            $this->event,
            $this->event->setResponse($value = new Response())
        );

        static::assertSame(
            $value,
            $this->event->getResponse()
        );

        static::assertTrue($this->event->isPropagationStopped());
    }
}

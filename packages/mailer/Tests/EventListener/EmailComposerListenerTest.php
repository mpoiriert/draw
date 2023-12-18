<?php

namespace Draw\Component\Mailer\Tests\EventListener;

use Draw\Component\Mailer\EmailComposer;
use Draw\Component\Mailer\EventListener\EmailComposerListener;
use Draw\Component\Tester\MockTrait;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\Envelope;
use Symfony\Component\Mailer\Event\MessageEvent;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Header\Headers;
use Symfony\Component\Mime\Header\UnstructuredHeader;
use Symfony\Component\Mime\Message;
use Symfony\Component\Mime\RawMessage;

#[CoversClass(EmailComposerListener::class)]
class EmailComposerListenerTest extends TestCase
{
    use MockTrait;

    private EmailComposerListener $object;

    private EmailComposer&MockObject $emailComposer;

    protected function setUp(): void
    {
        $this->object = new EmailComposerListener(
            $this->emailComposer = $this->createMock(EmailComposer::class)
        );
    }

    public function testComposeMessageNotMessage(): void
    {
        $this->emailComposer
            ->expects(static::never())
            ->method('compose');

        $this->object->composeMessage(
            $this->createMessageEvent(
                $this->createMock(RawMessage::class)
            )
        );
    }

    public function testComposeMessageComposed(): void
    {
        $this->emailComposer
            ->expects(static::never())
            ->method('compose');

        $message = $this->createMock(Message::class);

        $message
            ->expects(static::once())
            ->method('getHeaders')
            ->willReturn($headers = new Headers());

        $headers->add(new UnstructuredHeader('X-DrawEmail', '1'));

        $this->object->composeMessage($this->createMessageEvent($message));
    }

    public function testComposeMessage(): void
    {
        $message = new TemplatedEmail();

        $event = $this->createMessageEvent($message);

        $this->emailComposer
            ->expects(static::once())
            ->method('compose');

        $this->object->composeMessage($event);

        $headers = $message->getHeaders();

        static::assertTrue($headers->has('X-DrawEmail'));
    }

    public function testComposeMessageQueued(): void
    {
        $this->emailComposer
            ->expects(static::never())
            ->method('compose');

        $message = $this->createMock(Message::class);

        $this->object->composeMessage($this->createMessageEvent($message, true));
    }

    private function createMessageEvent(RawMessage $message, bool $queue = false): MessageEvent
    {
        return new MessageEvent(
            $message,
            new Envelope(new Address('test@example.com'), [new Address('test@example.com')]),
            uniqid('transport-'),
            $queue
        );
    }
}

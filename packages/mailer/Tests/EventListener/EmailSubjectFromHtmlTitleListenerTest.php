<?php

namespace Draw\Component\Mailer\Tests\EventListener;

use Draw\Component\Mailer\EventListener\EmailSubjectFromHtmlTitleListener;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Mailer\Envelope;
use Symfony\Component\Mailer\Event\MessageEvent;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;
use Symfony\Component\Mime\RawMessage;

#[CoversClass(EmailSubjectFromHtmlTitleListener::class)]
class EmailSubjectFromHtmlTitleListenerTest extends TestCase
{
    private EmailSubjectFromHtmlTitleListener $object;

    protected function setUp(): void
    {
        $this->object = new EmailSubjectFromHtmlTitleListener();
    }

    public function testConstruct(): void
    {
        static::assertInstanceOf(
            EventSubscriberInterface::class,
            $this->object
        );
    }

    public function testGetSubscribedEvents(): void
    {
        static::assertSame(
            [
                MessageEvent::class => ['assignSubjectFromHtmlTitle', -2],
            ],
            $this->object::getSubscribedEvents()
        );
    }

    public function testAssignSubjectFromHtmlTitleNotEmail(): void
    {
        $this->object->assignSubjectFromHtmlTitle(
            $this->createMessageEvent(
                $this->createMock(RawMessage::class)
            )
        );

        $this->addToAssertionCount(1);
    }

    public function testAssignSubjectFromHtmlTitleAlreadyASubject(): void
    {
        $message = $this->createMock(Email::class);

        $message
            ->expects(static::once())
            ->method('getSubject')
            ->willReturn('Subject');

        $message
            ->expects(static::never())
            ->method('subject');

        $this->object->assignSubjectFromHtmlTitle($this->createMessageEvent($message));
    }

    public function testAssignSubjectFromHtmlTitleNoHtmlBody(): void
    {
        $message = $this->createMock(Email::class);

        $message
            ->expects(static::once())
            ->method('getHtmlBody')
            ->willReturn('');

        $message
            ->expects(static::never())
            ->method('subject');

        $this->object->assignSubjectFromHtmlTitle($this->createMessageEvent($message));
    }

    public function testAssignSubjectFromHtmlTitleNoTitle(): void
    {
        $message = $this->createMock(Email::class);

        $message
            ->expects(static::once())
            ->method('getHtmlBody')
            ->willReturn('<div></div>');

        $message
            ->expects(static::never())
            ->method('subject');

        $this->object->assignSubjectFromHtmlTitle($this->createMessageEvent($message));
    }

    public function testAssignSubjectFromHtmlTitleTitleEmpty(): void
    {
        $message = $this->createMock(Email::class);

        $message
            ->expects(static::once())
            ->method('getHtmlBody')
            ->willReturn('<html lang="en"><head><title></title></head></html>');

        $message
            ->expects(static::never())
            ->method('subject');

        $this->object->assignSubjectFromHtmlTitle($this->createMessageEvent($message));
    }

    public function testAssignSubjectFromHtmlTitle(): void
    {
        $message = $this->createMock(Email::class);

        $message
            ->expects(static::once())
            ->method('getHtmlBody')
            ->willReturn('<html lang="en"><head><title>Title</title></head></html>');

        $message
            ->expects(static::once())
            ->method('subject')
            ->with('Title');

        $this->object->assignSubjectFromHtmlTitle($this->createMessageEvent($message));
    }

    private function createMessageEvent(RawMessage $message): MessageEvent
    {
        return new MessageEvent(
            $message,
            new Envelope(new Address('test@example.com'), [new Address('test@example.com')]),
            uniqid('transport-')
        );
    }
}

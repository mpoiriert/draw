<?php

namespace Draw\Component\Mailer\Tests\EventListener;

use Draw\Component\Mailer\EventListener\EmailCssInlinerListener;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Mailer\Envelope;
use Symfony\Component\Mailer\Event\MessageEvent;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;
use Symfony\Component\Mime\RawMessage;

#[CoversClass(EmailCssInlinerListener::class)]
class EmailCssInlinerListenerTest extends TestCase
{
    private EmailCssInlinerListener $object;

    protected function setUp(): void
    {
        $this->object = new EmailCssInlinerListener();
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
                MessageEvent::class => ['inlineEmailCss', -1],
            ],
            $this->object::getSubscribedEvents()
        );
    }

    public function testInlineEmailCssNotEmail(): void
    {
        $event = new MessageEvent(
            $this->createMock(RawMessage::class),
            new Envelope(new Address('test@example.com'), [new Address('test@example.com')]),
            uniqid('transport-')
        );

        $this->object->inlineEmailCss($event);

        $this->addToAssertionCount(1);
    }

    public function testInlineEmailCssNoHtmlBody(): void
    {
        $event = new MessageEvent(
            $message = $this->createMock(Email::class),
            new Envelope(new Address('test@example.com'), [new Address('test@example.com')]),
            uniqid('transport-')
        );

        $message
            ->expects(static::once())
            ->method('getHtmlBody')
            ->willReturn('');

        $message
            ->expects(static::never())
            ->method('html');

        $this->object->inlineEmailCss($event);
    }

    public function testInlineEmailCss(): void
    {
        /** @var Email&MockObject $message */
        $message = $this->createMock(Email::class);

        $event = new MessageEvent(
            $message,
            new Envelope(new Address('test@example.com'), [new Address('test@example.com')]),
            uniqid('transport-')
        );

        $message
            ->expects(static::once())
            ->method('getHtmlBody')
            ->willReturn('<html lang="en"><head><title></title><style>.body {background: maroon}</style></head><div class="body"></div></html>');

        $message
            ->expects(static::once())
            ->method('html')
            ->with('<!DOCTYPE html>
<html lang="en"><head><meta http-equiv="Content-Type" content="text/html; charset=utf-8"><title></title></head><body><div class="body" style="background: maroon;"></div></body></html>
');

        $this->object->inlineEmailCss($event);
    }
}

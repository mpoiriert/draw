<?php

namespace Draw\Bundle\TesterBundle\Mailer;

use Draw\Bundle\TesterBundle\Mailer\Constraint\TemplatedEmailCount;
use Symfony\Bundle\FrameworkBundle\Test\MailerAssertionsTrait;
use Symfony\Component\Mailer\Event\MessageEvent;
use Symfony\Component\Mime\Message;

trait TemplatedMailerAssertionsTrait
{
    use MailerAssertionsTrait;

    /**
     * @return MessageEvent[]
     */
    public static function getTemplatedMailerMailerEvents(string $type, ?string $template = null, ?string $transport = null): array
    {
        $events = [];
        foreach (self::getMessageMailerEvents()->getEvents($transport) as $event) {
            if ($event->isQueued()) {
                continue;
            }

            $message = $event->getMessage();

            if (!$message instanceof Message) {
                continue;
            }

            if (!$message->getHeaders()->has('X-DrawEmail-'.$type.'Template')) {
                continue;
            }

            if ($template && $message->getHeaders()->get('X-DrawEmail-'.$type.'Template')->getBodyAsString() !== $template) {
                continue;
            }

            $events[] = $event;
        }

        return $events;
    }

    /**
     * @return MessageEvent[]
     */
    public static function getTextTemplatedMailerMailerEvents(?string $template = null, ?string $transport = null): array
    {
        return static::getTemplatedMailerMailerEvents('Text', $template, $transport);
    }

    /**
     * @return MessageEvent[]
     */
    public static function getHtmlTemplatedMailerMailerEvents(?string $template = null, ?string $transport = null): array
    {
        return static::getTemplatedMailerMailerEvents('Html', $template, $transport);
    }

    public static function getTextTemplatedMailerEvent(int $index = 0, ?string $template = null, ?string $transport = null): ?MessageEvent
    {
        return static::getTextTemplatedMailerMailerEvents($template, $transport)[$index] ?? null;
    }

    public static function getHtmlTemplatedMailerEvent(int $index = 0, ?string $template = null, ?string $transport = null): ?MessageEvent
    {
        return static::getHtmlTemplatedMailerMailerEvents($template, $transport)[$index] ?? null;
    }

    public static function assertHtmlTemplatedEmailCount(int $count, ?string $template = null, ?string $transport = null, string $message = ''): void
    {
        self::assertThat(
            self::getMessageMailerEvents(),
            new TemplatedEmailCount($count, 'Html', $template, $transport),
            $message
        );
    }

    public static function assertTextTemplatedEmailCount(int $count, ?string $template = null, ?string $transport = null, string $message = ''): void
    {
        self::assertThat(
            self::getMessageMailerEvents(),
            new TemplatedEmailCount($count, 'Text', $template, $transport),
            $message
        );
    }
}

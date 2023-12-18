<?php

namespace App\Tests\Mailer\EventListener;

use Draw\Bundle\TesterBundle\EventDispatcher\EventListenerTestTrait;
use Draw\Component\Mailer\EventListener\EmailComposerListener;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Mailer\Event\MessageEvent;

class EmailComposerListenerTest extends KernelTestCase
{
    use EventListenerTestTrait;

    private EmailComposerListener $object;

    protected function setUp(): void
    {
        $this->object = static::getContainer()->get(EmailComposerListener::class);
    }

    public function testEventListenersRegistered(): void
    {
        static::assertEventListenersRegistered(
            $this->object::class,
            [
                MessageEvent::class => ['composeMessage'],
            ]
        );
    }
}

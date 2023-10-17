<?php

namespace Draw\Component\Security\Tests\Core\Event;

use Draw\Component\Security\Core\Event\CheckPreAuthEvent;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\User\UserInterface;

#[CoversClass(CheckPreAuthEvent::class)]
class CheckPreAuthEventTest extends TestCase
{
    private CheckPreAuthEvent $event;

    private UserInterface $user;

    protected function setUp(): void
    {
        $this->event = new CheckPreAuthEvent(
            $this->user = $this->createMock(UserInterface::class)
        );
    }

    public function testGetUser(): void
    {
        static::assertSame(
            $this->user,
            $this->event->getUser()
        );
    }
}

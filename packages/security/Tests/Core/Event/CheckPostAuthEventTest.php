<?php

namespace Draw\Component\Security\Tests\Core\Event;

use Draw\Component\Security\Core\Event\CheckPostAuthEvent;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @covers \Draw\Component\Security\Core\Event\CheckPostAuthEvent
 */
class CheckPostAuthEventTest extends TestCase
{
    private CheckPostAuthEvent $event;

    private UserInterface $user;

    public function setUp(): void
    {
        $this->event = new CheckPostAuthEvent(
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

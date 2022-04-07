<?php

namespace Draw\Component\Security\Tests\Core\Event;

use Draw\Component\Security\Core\Event\CheckPreAuthEvent;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @covers \Draw\Component\Security\Core\Event\CheckPreAuthEvent
 */
class CheckPreAuthEventTest extends TestCase
{
    private CheckPreAuthEvent $event;

    private UserInterface $user;

    public function setUp(): void
    {
        $this->event = new CheckPreAuthEvent(
            $this->user = $this->createMock(UserInterface::class)
        );
    }

    public function testGetUser(): void
    {
        $this->assertSame(
            $this->user,
            $this->event->getUser()
        );
    }
}

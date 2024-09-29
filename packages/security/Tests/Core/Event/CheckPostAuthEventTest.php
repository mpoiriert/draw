<?php

namespace Draw\Component\Security\Tests\Core\Event;

use Draw\Component\Security\Core\Event\CheckPostAuthEvent;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @internal
 */
#[CoversClass(CheckPostAuthEvent::class)]
class CheckPostAuthEventTest extends TestCase
{
    private CheckPostAuthEvent $event;

    private UserInterface $user;

    protected function setUp(): void
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

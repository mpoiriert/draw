<?php

namespace Draw\Component\Security\Tests\Core\User;

use Draw\Component\Security\Core\Event\CheckPostAuthEvent;
use Draw\Component\Security\Core\Event\CheckPreAuthEvent;
use Draw\Component\Security\Core\User\EventDrivenUserChecker;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\User\UserCheckerInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

/**
 * @internal
 */
#[CoversClass(EventDrivenUserChecker::class)]
class EventDrivenUserCheckerTest extends TestCase
{
    private EventDrivenUserChecker $object;

    private UserCheckerInterface&MockObject $decoratedUserChecker;

    private EventDispatcherInterface&MockObject $eventDispatcher;

    protected function setUp(): void
    {
        $this->object = new EventDrivenUserChecker(
            $this->decoratedUserChecker = $this->createMock(UserCheckerInterface::class),
            $this->eventDispatcher = $this->createMock(EventDispatcherInterface::class)
        );
    }

    public function testConstruct(): void
    {
        static::assertInstanceOf(
            UserCheckerInterface::class,
            $this->object
        );
    }

    public function testCheckPreAuth(): void
    {
        $user = $this->createMock(UserInterface::class);

        $this->decoratedUserChecker
            ->expects(static::once())
            ->method('checkPreAuth')
            ->with($user)
        ;

        $this->eventDispatcher
            ->expects(static::once())
            ->method('dispatch')
            ->with(
                static::callback(function (CheckPreAuthEvent $event) use ($user) {
                    $this->assertSame(
                        $user,
                        $event->getUser()
                    );

                    return true;
                })
            )
            ->willReturnArgument(0)
        ;

        $this->object->checkPreAuth($user);
    }

    public function testCheckPostAuth(): void
    {
        $user = $this->createMock(UserInterface::class);

        $this->decoratedUserChecker
            ->expects(static::once())
            ->method('checkPostAuth')
            ->with($user)
        ;

        $this->eventDispatcher
            ->expects(static::once())
            ->method('dispatch')
            ->with(
                static::callback(function (CheckPostAuthEvent $event) use ($user) {
                    $this->assertSame(
                        $user,
                        $event->getUser()
                    );

                    return true;
                })
            )
            ->willReturnArgument(0)
        ;

        $this->object->checkPostAuth($user);
    }
}

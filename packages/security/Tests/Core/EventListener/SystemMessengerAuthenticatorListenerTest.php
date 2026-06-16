<?php

namespace Draw\Component\Security\Tests\Core\EventListener;

use Draw\Component\Security\Core\Authentication\SystemAuthenticatorInterface;
use Draw\Component\Security\Core\EventListener\SystemMessengerAuthenticatorListener;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Messenger\Event\WorkerMessageReceivedEvent;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

/**
 * @internal
 */
class SystemMessengerAuthenticatorListenerTest extends TestCase
{
    public function testGetSubscribedEvents(): void
    {
        $object = new SystemMessengerAuthenticatorListener(
            static::createStub(TokenStorageInterface::class),
            static::createStub(SystemAuthenticatorInterface::class)
        );

        static::assertSame(
            [
                WorkerMessageReceivedEvent::class => 'connectSystem',
            ],
            $object::getSubscribedEvents()
        );
    }

    public function testConnectSystemAlreadyConnected(): void
    {
        $object = new SystemMessengerAuthenticatorListener(
            $tokenStorage = $this->createMock(TokenStorageInterface::class),
            static::createStub(SystemAuthenticatorInterface::class)
        );

        $tokenStorage
            ->expects(static::once())
            ->method('getToken')
            ->willReturn(static::createStub(TokenInterface::class))
        ;

        $tokenStorage
            ->expects(static::never())
            ->method('setToken')
            ->seal()
        ;

        $object->connectSystem();
    }

    public function testConnectSystemNotConnected(): void
    {
        $object = new SystemMessengerAuthenticatorListener(
            $tokenStorage = $this->createMock(TokenStorageInterface::class),
            $systemAuthenticator = $this->createMock(SystemAuthenticatorInterface::class)
        );

        $tokenStorage
            ->expects(static::once())
            ->method('getToken')
            ->willReturn(null)
        ;

        $tokenStorage
            ->expects(static::once())
            ->method('setToken')
            ->with($token = static::createStub(TokenInterface::class))
            ->seal()
        ;

        $systemAuthenticator
            ->expects(static::once())
            ->method('getTokenForSystem')
            ->willReturn($token)
            ->seal()
        ;

        $object->connectSystem();
    }
}

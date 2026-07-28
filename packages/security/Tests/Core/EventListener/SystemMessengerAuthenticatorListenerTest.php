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
            $this->createStub(TokenStorageInterface::class),
            $this->createStub(SystemAuthenticatorInterface::class)
        );

        $this->assertSame(
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
            $this->createStub(SystemAuthenticatorInterface::class)
        );

        $tokenStorage
            ->expects($this->once())
            ->method('getToken')
            ->willReturn($this->createStub(TokenInterface::class))
        ;

        $tokenStorage
            ->expects($this->never())
            ->method('setToken')
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
            ->expects($this->once())
            ->method('getToken')
            ->willReturn(null)
        ;

        $tokenStorage
            ->expects($this->once())
            ->method('setToken')
            ->with($token = $this->createStub(TokenInterface::class))
        ;

        $systemAuthenticator
            ->expects($this->once())
            ->method('getTokenForSystem')
            ->willReturn($token)
        ;

        $object->connectSystem();
    }
}

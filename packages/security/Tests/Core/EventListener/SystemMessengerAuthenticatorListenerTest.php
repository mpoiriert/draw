<?php

namespace Draw\Component\Security\Tests\Core\EventListener;

use Draw\Component\Security\Core\Authentication\SystemAuthenticatorInterface;
use Draw\Component\Security\Core\EventListener\SystemMessengerAuthenticatorListener;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Messenger\Event\WorkerMessageReceivedEvent;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

/**
 * @internal
 */
class SystemMessengerAuthenticatorListenerTest extends TestCase
{
    private SystemMessengerAuthenticatorListener $object;

    private MockObject&TokenStorageInterface $tokenStorage;

    private MockObject&SystemAuthenticatorInterface $systemAuthenticator;

    protected function setUp(): void
    {
        $this->object = new SystemMessengerAuthenticatorListener(
            $this->tokenStorage = $this->createMock(TokenStorageInterface::class),
            $this->systemAuthenticator = $this->createMock(SystemAuthenticatorInterface::class)
        );
    }

    public function testConstruct(): void
    {
        $this->assertInstanceOf(
            EventSubscriberInterface::class,
            $this->object
        );
    }

    public function testGetSubscribedEvents(): void
    {
        $this->assertSame(
            [
                WorkerMessageReceivedEvent::class => 'connectSystem',
            ],
            $this->object::getSubscribedEvents()
        );
    }

    public function testConnectSystemAlreadyConnected(): void
    {
        $this->tokenStorage
            ->expects($this->once())
            ->method('getToken')
            ->willReturn($this->createMock(TokenInterface::class))
        ;

        $this->tokenStorage
            ->expects($this->never())
            ->method('setToken')
        ;

        $this->object->connectSystem();
    }

    public function testConnectSystemNotConnected(): void
    {
        $this->tokenStorage
            ->expects($this->once())
            ->method('getToken')
            ->willReturn(null)
        ;

        $this->tokenStorage
            ->expects($this->once())
            ->method('setToken')
            ->with($token = $this->createMock(TokenInterface::class))
        ;

        $this->systemAuthenticator
            ->expects($this->once())
            ->method('getTokenForSystem')
            ->willReturn($token)
        ;

        $this->object->connectSystem();
    }
}

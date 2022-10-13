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

class SystemMessengerAuthenticatorListenerTest extends TestCase
{
    private SystemMessengerAuthenticatorListener $object;

    /**
     * @var TokenStorageInterface&MockObject
     */
    private $tokenStorage;

    /**
     * @var SystemAuthenticatorInterface&MockObject
     */
    private $systemAuthenticator;

    protected function setUp(): void
    {
        $this->object = new SystemMessengerAuthenticatorListener(
            $this->tokenStorage = $this->createMock(TokenStorageInterface::class),
            $this->systemAuthenticator = $this->createMock(SystemAuthenticatorInterface::class)
        );
    }

    public function testConstruct(): void
    {
        static::assertInstanceOf(
            EventSubscriberInterface::class,
            $this->object
        );
    }

    public function testGetSubscribedEvents(): void
    {
        static::assertSame(
            [
                WorkerMessageReceivedEvent::class => 'connectSystem',
            ],
            $this->object::getSubscribedEvents()
        );
    }

    public function testConnectSystemAlreadyConnected(): void
    {
        $this->tokenStorage
            ->expects(static::once())
            ->method('getToken')
            ->willReturn($this->createMock(TokenInterface::class));

        $this->tokenStorage
            ->expects(static::never())
            ->method('setToken');

        $this->object->connectSystem();
    }

    public function testConnectSystemNotConnected(): void
    {
        $this->tokenStorage
            ->expects(static::once())
            ->method('getToken')
            ->willReturn(null);

        $this->tokenStorage
            ->expects(static::once())
            ->method('setToken')
            ->with($token = $this->createMock(TokenInterface::class));

        $this->systemAuthenticator
            ->expects(static::once())
            ->method('getTokenForSystem')
            ->willReturn($token);

        $this->object->connectSystem();
    }
}

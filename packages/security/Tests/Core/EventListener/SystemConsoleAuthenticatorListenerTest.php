<?php

namespace Core\EventListener;

use Draw\Component\Core\Reflection\ReflectionAccessor;
use Draw\Component\Security\Core\Authentication\SystemAuthenticatorInterface;
use Draw\Component\Security\Core\EventListener\SystemConsoleAuthenticatorListener;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Event\ConsoleCommandEvent;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class SystemConsoleAuthenticatorListenerTest extends TestCase
{
    private SystemConsoleAuthenticatorListener $object;

    private MockObject&TokenStorageInterface $tokenStorage;

    private MockObject&SystemAuthenticatorInterface $systemAuthenticator;

    protected function setUp(): void
    {
        $this->object = new SystemConsoleAuthenticatorListener(
            $this->tokenStorage = $this->createMock(TokenStorageInterface::class),
            $this->systemAuthenticator = $this->createMock(SystemAuthenticatorInterface::class),
            true
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
                ConsoleCommandEvent::class => [
                    ['addOptions', 255],
                    ['connectSystem', 0],
                ],
            ],
            $this->object::getSubscribedEvents()
        );
    }

    public function testAddOptions(): void
    {
        $consoleCommandEvent = $this->createConsoleCommandEvent();

        $this->object->addOptions($consoleCommandEvent);

        $definition = $consoleCommandEvent->getCommand()->getDefinition();

        $option = $definition->getOption('as-system');

        static::assertNull($option->getShortcut());
        static::assertTrue(\strlen($option->getDescription()) > 10);
        static::assertFalse($option->acceptValue());
        static::assertFalse($option->getDefault());
    }

    public function testConnectSystemAutoConnect(): void
    {
        ReflectionAccessor::setPropertiesValue($this->object, ['systemAutoLogin' => true]);

        $consoleCommandEvent = $this->createConsoleCommandEvent();

        $this->tokenStorage
            ->expects(static::once())
            ->method('getToken')
            ->willReturn(null);

        $this->systemAuthenticator
            ->expects(static::once())
            ->method('getTokenForSystem')
            ->willReturn($token = $this->createMock(TokenInterface::class));

        $this->tokenStorage
            ->expects(static::once())
            ->method('setToken')
            ->with($token);

        $this->object->connectSystem($consoleCommandEvent);
    }

    public function testConnectSystemAutoConnectDisabled(): void
    {
        ReflectionAccessor::setPropertiesValue($this->object, ['systemAutoLogin' => false]);

        $consoleCommandEvent = $this->createConsoleCommandEvent();

        $this->tokenStorage
            ->expects(static::never())
            ->method('getToken')
            ->willReturn(null);

        $this->systemAuthenticator
            ->expects(static::never())
            ->method('getTokenForSystem');

        $this->tokenStorage
            ->expects(static::never())
            ->method('setToken');

        $this->object->connectSystem($consoleCommandEvent);
    }

    public function testConnectSystemAutoConnectAlreadyConnected(): void
    {
        ReflectionAccessor::setPropertiesValue($this->object, ['systemAutoLogin' => true]);

        $consoleCommandEvent = $this->createConsoleCommandEvent();

        $this->tokenStorage
            ->expects(static::once())
            ->method('getToken')
            ->willReturn($this->createMock(TokenInterface::class));

        $this->systemAuthenticator
            ->expects(static::never())
            ->method('getTokenForSystem');

        $this->tokenStorage
            ->expects(static::never())
            ->method('setToken');

        $this->object->connectSystem($consoleCommandEvent);
    }

    public function testConnectSystemAutoConnectWithOption(): void
    {
        ReflectionAccessor::setPropertiesValue($this->object, ['systemAutoLogin' => false]);

        $consoleCommandEvent = $this->createConsoleCommandEvent(['--as-system' => true]);

        $this->object->addOptions($consoleCommandEvent);

        $this->tokenStorage
            ->expects(static::once())
            ->method('getToken')
            ->willReturn(null);

        $this->systemAuthenticator
            ->expects(static::once())
            ->method('getTokenForSystem')
            ->willReturn($token = $this->createMock(TokenInterface::class));

        $this->tokenStorage
            ->expects(static::once())
            ->method('setToken')
            ->with($token);

        $this->object->connectSystem($consoleCommandEvent);
    }

    public function testConnectSystemAutoConnectWithOptionAndSystemAutoLogin(): void
    {
        ReflectionAccessor::setPropertiesValue($this->object, ['systemAutoLogin' => true]);

        $consoleCommandEvent = $this->createConsoleCommandEvent(['--as-system' => true]);

        $this->object->addOptions($consoleCommandEvent);

        $this->tokenStorage
            ->expects(static::once())
            ->method('getToken')
            ->willReturn(null);

        $this->systemAuthenticator
            ->expects(static::once())
            ->method('getTokenForSystem')
            ->willReturn($token = $this->createMock(TokenInterface::class));

        $this->tokenStorage
            ->expects(static::once())
            ->method('setToken')
            ->with($token);

        $this->object->connectSystem($consoleCommandEvent);
    }

    protected function createConsoleCommandEvent(array $input = []): ConsoleCommandEvent
    {
        return new ConsoleCommandEvent(
            new class extends Command {},
            new ArrayInput($input),
            new NullOutput()
        );
    }
}

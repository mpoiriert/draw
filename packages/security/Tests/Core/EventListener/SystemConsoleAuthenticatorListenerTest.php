<?php

namespace Draw\Component\Security\Tests\Core\EventListener;

use Draw\Component\Security\Core\Authentication\SystemAuthenticatorInterface;
use Draw\Component\Security\Core\EventListener\SystemConsoleAuthenticatorListener;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Event\ConsoleCommandEvent;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

/**
 * @internal
 */
class SystemConsoleAuthenticatorListenerTest extends TestCase
{
    public function testGetSubscribedEvents(): void
    {
        $object = new SystemConsoleAuthenticatorListener(
            static::createStub(TokenStorageInterface::class),
            static::createStub(SystemAuthenticatorInterface::class),
            true
        );

        static::assertSame(
            [
                ConsoleCommandEvent::class => [
                    ['addOptions', 255],
                    ['connectSystem', 0],
                ],
            ],
            $object::getSubscribedEvents()
        );
    }

    public function testAddOptions(): void
    {
        $object = new SystemConsoleAuthenticatorListener(
            static::createStub(TokenStorageInterface::class),
            static::createStub(SystemAuthenticatorInterface::class),
            true
        );

        $consoleCommandEvent = $this->createConsoleCommandEvent();

        $object->addOptions($consoleCommandEvent);

        $definition = $consoleCommandEvent->getCommand()->getDefinition();

        $option = $definition->getOption('as-system');

        static::assertNull($option->getShortcut());
        static::assertTrue(\strlen($option->getDescription()) > 10);
        static::assertFalse($option->acceptValue());
        static::assertFalse($option->getDefault());
    }

    public function testConnectSystemAutoConnect(): void
    {
        $object = new SystemConsoleAuthenticatorListener(
            $tokenStorage = $this->createMock(TokenStorageInterface::class),
            $systemAuthenticator = $this->createMock(SystemAuthenticatorInterface::class),
            true
        );

        $consoleCommandEvent = $this->createConsoleCommandEvent();

        $tokenStorage
            ->expects(static::once())
            ->method('getToken')
            ->willReturn(null)
        ;

        $systemAuthenticator
            ->expects(static::once())
            ->method('getTokenForSystem')
            ->willReturn($token = static::createStub(TokenInterface::class))
        ;

        $tokenStorage
            ->expects(static::once())
            ->method('setToken')
            ->with($token)
        ;

        $object->connectSystem($consoleCommandEvent);
    }

    public function testConnectSystemAutoConnectDisabled(): void
    {
        $object = new SystemConsoleAuthenticatorListener(
            $tokenStorage = $this->createMock(TokenStorageInterface::class),
            $systemAuthenticator = $this->createMock(SystemAuthenticatorInterface::class),
            false
        );

        $consoleCommandEvent = $this->createConsoleCommandEvent();

        $tokenStorage
            ->expects(static::never())
            ->method('getToken')
            ->willReturn(null)
        ;

        $systemAuthenticator
            ->expects(static::never())
            ->method('getTokenForSystem')
        ;

        $tokenStorage
            ->expects(static::never())
            ->method('setToken')
        ;

        $object->connectSystem($consoleCommandEvent);
    }

    public function testConnectSystemAutoConnectAlreadyConnected(): void
    {
        $object = new SystemConsoleAuthenticatorListener(
            $tokenStorage = $this->createMock(TokenStorageInterface::class),
            $systemAuthenticator = $this->createMock(SystemAuthenticatorInterface::class),
            true
        );

        $consoleCommandEvent = $this->createConsoleCommandEvent();

        $tokenStorage
            ->expects(static::once())
            ->method('getToken')
            ->willReturn(static::createStub(TokenInterface::class))
        ;

        $systemAuthenticator
            ->expects(static::never())
            ->method('getTokenForSystem')
        ;

        $tokenStorage
            ->expects(static::never())
            ->method('setToken')
        ;

        $object->connectSystem($consoleCommandEvent);
    }

    public function testConnectSystemAutoConnectWithOption(): void
    {
        $object = new SystemConsoleAuthenticatorListener(
            $tokenStorage = $this->createMock(TokenStorageInterface::class),
            $systemAuthenticator = $this->createMock(SystemAuthenticatorInterface::class),
            false
        );

        $consoleCommandEvent = $this->createConsoleCommandEvent(['--as-system' => true]);

        $object->addOptions($consoleCommandEvent);

        $tokenStorage
            ->expects(static::once())
            ->method('getToken')
            ->willReturn(null)
        ;

        $systemAuthenticator
            ->expects(static::once())
            ->method('getTokenForSystem')
            ->willReturn($token = static::createStub(TokenInterface::class))
        ;

        $tokenStorage
            ->expects(static::once())
            ->method('setToken')
            ->with($token)
        ;

        $object->connectSystem($consoleCommandEvent);
    }

    public function testConnectSystemAutoConnectWithOptionAndSystemAutoLogin(): void
    {
        $object = new SystemConsoleAuthenticatorListener(
            $tokenStorage = $this->createMock(TokenStorageInterface::class),
            $systemAuthenticator = $this->createMock(SystemAuthenticatorInterface::class),
            false
        );

        $consoleCommandEvent = $this->createConsoleCommandEvent(['--as-system' => true]);

        $object->addOptions($consoleCommandEvent);

        $tokenStorage
            ->expects(static::once())
            ->method('getToken')
            ->willReturn(null)
        ;

        $systemAuthenticator
            ->expects(static::once())
            ->method('getTokenForSystem')
            ->willReturn($token = static::createStub(TokenInterface::class))
        ;

        $tokenStorage
            ->expects(static::once())
            ->method('setToken')
            ->with($token)
        ;

        $object->connectSystem($consoleCommandEvent);
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

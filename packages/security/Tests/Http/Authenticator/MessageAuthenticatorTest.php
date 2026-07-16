<?php

namespace Draw\Component\Security\Tests\Http\Authenticator;

use Draw\Component\Messenger\Searchable\EnvelopeFinder;
use Draw\Component\Security\Core\Security;
use Draw\Component\Security\Http\Authenticator\MessageAuthenticator;
use Draw\Component\Security\Http\Message\AutoConnectInterface;
use Draw\Contracts\Messenger\Exception\MessageNotFoundException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Http\Authenticator\AbstractAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;
use Symfony\Component\Security\Http\Authenticator\Token\PostAuthenticationToken;

/**
 * @internal
 */
#[CoversClass(MessageAuthenticator::class)]
class MessageAuthenticatorTest extends TestCase
{
    public function testSupportsNoConnectedUser(): void
    {
        $service = new MessageAuthenticator(
            $envelopeFinder = $this->createMock(EnvelopeFinder::class),
            $userProvider = $this->createMock(UserProviderInterface::class),
            $security = $this->createMock(Security::class),
        );

        $request = new Request();
        $request->query->set('dMUuid', $messageId = uniqid('message-id'));

        $security
            ->expects(static::once())
            ->method('getUser')
            ->willReturn(null)
        ;

        $envelopeFinder
            ->expects(static::once())
            ->method('findById')
            ->with($messageId)
            ->willReturn(new Envelope($this->createAutoConnectMessage($userIdentifier = uniqid('user-id-'))))
        ;

        $userProvider
            ->expects(static::once())
            ->method('loadUserByIdentifier')
            ->with($userIdentifier)
            ->willReturn(static::createStub(UserInterface::class))
        ;

        static::assertTrue($service->supports($request));
    }

    public function testSupportsDifferentUser(): void
    {
        $service = new MessageAuthenticator(
            $envelopeFinder = $this->createMock(EnvelopeFinder::class),
            $userProvider = $this->createMock(UserProviderInterface::class),
            $security = $this->createMock(Security::class),
        );

        $request = new Request();
        $request->query->set('dMUuid', $messageId = uniqid('message-id'));

        $security
            ->expects(static::once())
            ->method('getUser')
            ->willReturn(static::createStub(UserInterface::class))
        ;

        $envelopeFinder
            ->expects(static::once())
            ->method('findById')
            ->with($messageId)
            ->willReturn(new Envelope($this->createAutoConnectMessage($userIdentifier = uniqid('user-id-'))))
        ;

        $userProvider
            ->expects(static::once())
            ->method('loadUserByIdentifier')
            ->with($userIdentifier)
            ->willReturn(static::createStub(UserInterface::class))
        ;

        static::assertTrue($service->supports($request));
    }

    public function testSupportsNoMessageParameter(): void
    {
        $service = new MessageAuthenticator(
            static::createStub(EnvelopeFinder::class),
            static::createStub(UserProviderInterface::class),
            static::createStub(Security::class),
        );

        static::assertFalse($service->supports(new Request()));
    }

    public function testSupportsSameUser(): void
    {
        $service = new MessageAuthenticator(
            $envelopeFinder = $this->createMock(EnvelopeFinder::class),
            $userProvider = $this->createMock(UserProviderInterface::class),
            $security = $this->createMock(Security::class),
        );

        $request = new Request();
        $request->query->set('dMUuid', $messageId = uniqid('message-id'));

        $security
            ->expects(static::once())
            ->method('getUser')
            ->willReturn($user = static::createStub(UserInterface::class))
        ;

        $envelopeFinder
            ->expects(static::once())
            ->method('findById')
            ->with($messageId)
            ->willReturn(new Envelope($this->createAutoConnectMessage($userIdentifier = uniqid('user-id-'))))
        ;

        $userProvider
            ->expects(static::once())
            ->method('loadUserByIdentifier')
            ->with($userIdentifier)
            ->willReturn($user)
        ;

        static::assertFalse($service->supports($request));
    }

    public function testSupportsNoMessage(): void
    {
        $service = new MessageAuthenticator(
            $envelopeFinder = $this->createMock(EnvelopeFinder::class),
            static::createStub(UserProviderInterface::class),
            static::createStub(Security::class),
        );

        $request = new Request();
        $request->query->set('dMUuid', $messageId = uniqid('message-id'));

        $envelopeFinder
            ->expects(static::once())
            ->method('findById')
            ->with($messageId)
            ->willThrowException(new MessageNotFoundException($messageId))
        ;

        static::assertFalse($service->supports($request));
    }

    public function testAuthenticateNoMessage(): void
    {
        $service = new MessageAuthenticator(
            $envelopeFinder = $this->createMock(EnvelopeFinder::class),
            static::createStub(UserProviderInterface::class),
            static::createStub(Security::class),
        );

        $request = new Request();
        $request->query->set('dMUuid', $messageId = uniqid('message-id'));

        $envelopeFinder
            ->expects(static::once())
            ->method('findById')
            ->with($messageId)
            ->willThrowException(new MessageNotFoundException($messageId))
        ;

        $this->expectException(CustomUserMessageAuthenticationException::class);
        $this->expectExceptionMessage('Invalid message id.');

        $service->authenticate($request);
    }

    public function testAuthenticate(): void
    {
        $service = new MessageAuthenticator(
            $envelopeFinder = $this->createMock(EnvelopeFinder::class),
            $userProvider = $this->createMock(UserProviderInterface::class),
            static::createStub(Security::class),
        );

        $request = new Request();
        $request->query->set('dMUuid', $messageId = uniqid('message-id'));

        $envelopeFinder
            ->expects(static::once())
            ->method('findById')
            ->with($messageId)
            ->willReturn(new Envelope($this->createAutoConnectMessage($userIdentifier = uniqid('user-id-'))))
        ;

        $user = $this->createMock(UserInterface::class);

        $user
            ->expects(static::once())
            ->method('getUserIdentifier')
            ->willReturn($userIdentifier)
        ;

        $userProvider
            ->expects(static::once())
            ->method('loadUserByIdentifier')
            ->with($userIdentifier)
            ->willReturn($user)
        ;

        $passport = $service->authenticate($request);

        static::assertInstanceOf(
            SelfValidatingPassport::class,
            $passport
        );

        $userBadge = $passport->getBadge(UserBadge::class);

        static::assertSame(
            $userIdentifier.'+message-'.$messageId,
            $userBadge->getUserIdentifier()
        );

        static::assertSame(
            $user,
            $userBadge->getUser()
        );
    }

    public function testOnAuthenticationSuccess(): void
    {
        $service = new MessageAuthenticator(
            static::createStub(EnvelopeFinder::class),
            static::createStub(UserProviderInterface::class),
            static::createStub(Security::class),
        );

        static::assertNull(
            $service->onAuthenticationSuccess(
                new Request(),
                static::createStub(TokenInterface::class),
                uniqid('firewall-')
            )
        );
    }

    public function testOnAuthenticationFailure(): void
    {
        $service = new MessageAuthenticator(
            static::createStub(EnvelopeFinder::class),
            static::createStub(UserProviderInterface::class),
            static::createStub(Security::class),
        );

        static::assertNull(
            $service->onAuthenticationFailure(
                new Request(),
                new CustomUserMessageAuthenticationException()
            )
        );
    }

    /**
     * This is form the parent abstract class, but we test it as part of a contract test.
     *
     * @see AbstractAuthenticator
     */
    public function testCreateToken(): void
    {
        $service = new MessageAuthenticator(
            static::createStub(EnvelopeFinder::class),
            static::createStub(UserProviderInterface::class),
            static::createStub(Security::class),
        );

        $passport = static::createStub(Passport::class);
        $passport
            ->method('getUser')
            ->willReturn($user = static::createStub(UserInterface::class))
        ;

        $user
            ->method('getRoles')
            ->willReturn($roles = [uniqid('ROLE_')])
        ;

        $token = $service->createToken(
            $passport,
            $firewallName = uniqid('firewall-')
        );

        static::assertInstanceOf(
            PostAuthenticationToken::class,
            $token
        );

        static::assertSame(
            $roles,
            $token->getRoleNames()
        );

        static::assertSame(
            $user,
            $token->getUser()
        );

        static::assertSame(
            $firewallName,
            $token->getFirewallName()
        );
    }

    private function createAutoConnectMessage(string $userIdentifier): AutoConnectInterface
    {
        $message = static::createStub(AutoConnectInterface::class);

        $message
            ->method('getUserIdentifier')
            ->willReturn($userIdentifier)
        ;

        return $message;
    }
}

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
            ->expects($this->once())
            ->method('getUser')
            ->willReturn(null)
        ;

        $envelopeFinder
            ->expects($this->once())
            ->method('findById')
            ->with($messageId)
            ->willReturn(new Envelope($this->createAutoConnectMessage($userIdentifier = uniqid('user-id-'))))
        ;

        $userProvider
            ->expects($this->once())
            ->method('loadUserByIdentifier')
            ->with($userIdentifier)
            ->willReturn($this->createStub(UserInterface::class))
        ;

        $this->assertTrue($service->supports($request));
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
            ->expects($this->once())
            ->method('getUser')
            ->willReturn($this->createStub(UserInterface::class))
        ;

        $envelopeFinder
            ->expects($this->once())
            ->method('findById')
            ->with($messageId)
            ->willReturn(new Envelope($this->createAutoConnectMessage($userIdentifier = uniqid('user-id-'))))
        ;

        $userProvider
            ->expects($this->once())
            ->method('loadUserByIdentifier')
            ->with($userIdentifier)
            ->willReturn($this->createStub(UserInterface::class))
        ;

        $this->assertTrue($service->supports($request));
    }

    public function testSupportsNoMessageParameter(): void
    {
        $service = new MessageAuthenticator(
            $this->createStub(EnvelopeFinder::class),
            $this->createStub(UserProviderInterface::class),
            $this->createStub(Security::class),
        );

        $this->assertFalse($service->supports(new Request()));
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
            ->expects($this->once())
            ->method('getUser')
            ->willReturn($user = $this->createStub(UserInterface::class))
        ;

        $envelopeFinder
            ->expects($this->once())
            ->method('findById')
            ->with($messageId)
            ->willReturn(new Envelope($this->createAutoConnectMessage($userIdentifier = uniqid('user-id-'))))
        ;

        $userProvider
            ->expects($this->once())
            ->method('loadUserByIdentifier')
            ->with($userIdentifier)
            ->willReturn($user)
        ;

        $this->assertFalse($service->supports($request));
    }

    public function testSupportsNoMessage(): void
    {
        $service = new MessageAuthenticator(
            $envelopeFinder = $this->createMock(EnvelopeFinder::class),
            $this->createStub(UserProviderInterface::class),
            $this->createStub(Security::class),
        );

        $request = new Request();
        $request->query->set('dMUuid', $messageId = uniqid('message-id'));

        $envelopeFinder
            ->expects($this->once())
            ->method('findById')
            ->with($messageId)
            ->willThrowException(new MessageNotFoundException($messageId))
        ;

        $this->assertFalse($service->supports($request));
    }

    public function testAuthenticateNoMessage(): void
    {
        $service = new MessageAuthenticator(
            $envelopeFinder = $this->createMock(EnvelopeFinder::class),
            $this->createStub(UserProviderInterface::class),
            $this->createStub(Security::class),
        );

        $request = new Request();
        $request->query->set('dMUuid', $messageId = uniqid('message-id'));

        $envelopeFinder
            ->expects($this->once())
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
            $this->createStub(Security::class),
        );

        $request = new Request();
        $request->query->set('dMUuid', $messageId = uniqid('message-id'));

        $envelopeFinder
            ->expects($this->once())
            ->method('findById')
            ->with($messageId)
            ->willReturn(new Envelope($this->createAutoConnectMessage($userIdentifier = uniqid('user-id-'))))
        ;

        $user = $this->createMock(UserInterface::class);

        $user
            ->expects($this->once())
            ->method('getUserIdentifier')
            ->willReturn($userIdentifier)
        ;

        $userProvider
            ->expects($this->once())
            ->method('loadUserByIdentifier')
            ->with($userIdentifier)
            ->willReturn($user)
        ;

        $passport = $service->authenticate($request);

        $this->assertInstanceOf(
            SelfValidatingPassport::class,
            $passport
        );

        $userBadge = $passport->getBadge(UserBadge::class);

        $this->assertSame(
            $userIdentifier.'+message-'.$messageId,
            $userBadge->getUserIdentifier()
        );

        $this->assertSame(
            $user,
            $userBadge->getUser()
        );
    }

    public function testOnAuthenticationSuccess(): void
    {
        $service = new MessageAuthenticator(
            $this->createStub(EnvelopeFinder::class),
            $this->createStub(UserProviderInterface::class),
            $this->createStub(Security::class),
        );

        $this->assertNull(
            $service->onAuthenticationSuccess(
                new Request(),
                $this->createStub(TokenInterface::class),
                uniqid('firewall-')
            )
        );
    }

    public function testOnAuthenticationFailure(): void
    {
        $service = new MessageAuthenticator(
            $this->createStub(EnvelopeFinder::class),
            $this->createStub(UserProviderInterface::class),
            $this->createStub(Security::class),
        );

        $this->assertNull(
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
            $this->createStub(EnvelopeFinder::class),
            $this->createStub(UserProviderInterface::class),
            $this->createStub(Security::class),
        );

        $passport = $this->createStub(Passport::class);
        $passport
            ->method('getUser')
            ->willReturn($user = $this->createStub(UserInterface::class))
        ;

        $user
            ->method('getRoles')
            ->willReturn($roles = [uniqid('ROLE_')])
        ;

        $token = $service->createToken(
            $passport,
            $firewallName = uniqid('firewall-')
        );

        $this->assertInstanceOf(
            PostAuthenticationToken::class,
            $token
        );

        $this->assertSame(
            $roles,
            $token->getRoleNames()
        );

        $this->assertSame(
            $user,
            $token->getUser()
        );

        $this->assertSame(
            $firewallName,
            $token->getFirewallName()
        );
    }

    private function createAutoConnectMessage(string $userIdentifier): AutoConnectInterface
    {
        $message = $this->createStub(AutoConnectInterface::class);

        $message
            ->method('getUserIdentifier')
            ->willReturn($userIdentifier)
        ;

        return $message;
    }
}

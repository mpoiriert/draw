<?php

namespace Draw\Component\Security\Tests\Http\Authenticator;

use Draw\Component\Messenger\Searchable\EnvelopeFinder;
use Draw\Component\Security\Http\Authenticator\MessageAuthenticator;
use Draw\Component\Security\Http\Message\AutoConnectInterface;
use Draw\Component\Tester\MockBuilderTrait;
use Draw\Contracts\Messenger\Exception\MessageNotFoundException;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Http\Authenticator\AbstractAuthenticator;
use Symfony\Component\Security\Http\Authenticator\AuthenticatorInterface;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;
use Symfony\Component\Security\Http\Authenticator\Token\PostAuthenticationToken;

/**
 * @covers \Draw\Component\Security\Http\Authenticator\MessageAuthenticator
 */
class MessageAuthenticatorTest extends TestCase
{
    use MockBuilderTrait;

    private MessageAuthenticator $service;

    private EnvelopeFinder $envelopeFinder;

    private UserProviderInterface $userProvider;

    private Security $security;

    public function setUp(): void
    {
        $this->userProvider = $this->createMockWithExtraMethods(
            UserProviderInterface::class,
            ['loadUserByIdentifier']
        );

        $this->service = new MessageAuthenticator(
            $this->envelopeFinder = $this->createMock(EnvelopeFinder::class),
            $this->userProvider,
            $this->security = $this->createMock(Security::class),
        );
    }

    public function testConstruct(): void
    {
        $this->assertInstanceOf(
            AuthenticatorInterface::class,
            $this->service
        );
    }

    public function testSupportsNoConnectedUser(): void
    {
        $request = new Request();
        $request->query->set('dMUuid', $messageId = uniqid('message-id'));

        $this->security
            ->expects($this->once())
            ->method('getUser')
            ->willReturn(null);

        $this->envelopeFinder
            ->expects($this->once())
            ->method('findById')
            ->with($messageId)
            ->willReturn(new Envelope($this->createAutoConnectMessage($userIdentifier = uniqid('user-id-'))));

        $this->userProvider
            ->expects($this->once())
            ->method('loadUserByIdentifier')
            ->with($userIdentifier)
            ->willReturn($this->createMock(UserInterface::class));

        $this->assertTrue($this->service->supports($request));
    }

    public function testSupportsDifferentUser(): void
    {
        $request = new Request();
        $request->query->set('dMUuid', $messageId = uniqid('message-id'));

        $this->security
            ->expects($this->once())
            ->method('getUser')
            ->willReturn($this->createMock(UserInterface::class));

        $this->envelopeFinder
            ->expects($this->once())
            ->method('findById')
            ->with($messageId)
            ->willReturn(new Envelope($this->createAutoConnectMessage($userIdentifier = uniqid('user-id-'))));

        $this->userProvider
            ->expects($this->once())
            ->method('loadUserByIdentifier')
            ->with($userIdentifier)
            ->willReturn($this->createMock(UserInterface::class));

        $this->assertTrue($this->service->supports($request));
    }

    public function testSupportsNoMessageParameter(): void
    {
        $this->assertFalse($this->service->supports(new Request()));
    }

    public function testSupportsSameUser(): void
    {
        $request = new Request();
        $request->query->set('dMUuid', $messageId = uniqid('message-id'));

        $this->security
            ->expects($this->once())
            ->method('getUser')
            ->willReturn($user = $this->createMock(UserInterface::class));

        $this->envelopeFinder
            ->expects($this->once())
            ->method('findById')
            ->with($messageId)
            ->willReturn(new Envelope($this->createAutoConnectMessage($userIdentifier = uniqid('user-id-'))));

        $this->userProvider
            ->expects($this->once())
            ->method('loadUserByIdentifier')
            ->with($userIdentifier)
            ->willReturn($user);

        $this->assertFalse($this->service->supports($request));
    }

    public function testSupportsNoMessage(): void
    {
        $request = new Request();
        $request->query->set('dMUuid', $messageId = uniqid('message-id'));

        $this->envelopeFinder
            ->expects($this->once())
            ->method('findById')
            ->with($messageId)
            ->willThrowException(new MessageNotFoundException($messageId));

        $this->assertFalse($this->service->supports($request));
    }

    public function testAuthenticateNoMessage(): void
    {
        $request = new Request();
        $request->query->set('dMUuid', $messageId = uniqid('message-id'));

        $this->envelopeFinder
            ->expects($this->once())
            ->method('findById')
            ->with($messageId)
            ->willThrowException(new MessageNotFoundException($messageId));

        $this->expectException(CustomUserMessageAuthenticationException::class);
        $this->expectExceptionMessage('Invalid message id.');

        $this->service->authenticate($request);
    }

    public function testAuthenticate(): void
    {
        $request = new Request();
        $request->query->set('dMUuid', $messageId = uniqid('message-id'));

        $this->envelopeFinder
            ->expects($this->once())
            ->method('findById')
            ->with($messageId)
            ->willReturn(new Envelope($this->createAutoConnectMessage($userIdentifier = uniqid('user-id-'))));

        $user = $this->createMockWithExtraMethods(
            UserInterface::class,
            ['getUserIdentifier']
        );

        $user
            ->expects($this->once())
            ->method('getUserIdentifier')
            ->willReturn($userIdentifier);

        $this->userProvider
            ->expects($this->once())
            ->method('loadUserByIdentifier')
            ->with($userIdentifier)
            ->willReturn($user);

        $passport = $this->service->authenticate($request);

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
        $this->assertNull(
            $this->service->onAuthenticationSuccess(
                new Request(),
                $this->createMock(TokenInterface::class),
                uniqid('firewall-')
            )
        );
    }

    public function testOnAuthenticationFailure(): void
    {
        $this->assertNull(
            $this->service->onAuthenticationFailure(
                new Request(),
                new CustomUserMessageAuthenticationException()
            )
        );
    }

    /**
     * This is form the parent abstract class but we test it as part of a contract test.
     *
     * @see AbstractAuthenticator
     */
    public function testCreateToken(): void
    {
        $passport = $this->createMock(Passport::class);
        $passport
            ->expects($this->any())
            ->method('getUser')
            ->willReturn($user = $this->createMock(UserInterface::class));

        $user
            ->expects($this->any())
            ->method('getRoles')
            ->willReturn($roles = [uniqid('ROLE_')]);

        $token = $this->service->createToken(
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
        $message = $this->createMock(AutoConnectInterface::class);

        $message->expects($this->any())
            ->method('getUserIdentifier')
            ->willReturn($userIdentifier);

        return $message;
    }
}

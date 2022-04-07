<?php

namespace Draw\Component\Security\Tests\Http\Authenticator;

use Draw\Bundle\MessengerBundle\Controller\MessageController;
use Draw\Bundle\UserBundle\Message\AutoConnect;
use Draw\Component\Security\Http\Authenticator\MessageAuthenticator;
use Draw\Component\Tester\MockBuilderTrait;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Transport\Receiver\ListableReceiverInterface;
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

    private ListableReceiverInterface $transport;

    private UserProviderInterface $userProvider;

    private Security $security;

    public function setUp(): void
    {
        $this->userProvider = $this->createMockWithExtractMethods(
            UserProviderInterface::class,
            ['loadUserByIdentifier']
        );

        $this->service = new MessageAuthenticator(
            $this->transport = $this->createMock(ListableReceiverInterface::class),
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
        $request->query->set(MessageController::MESSAGE_ID_PARAMETER_NAME, uniqid('message-id'));

        $this->security
            ->expects($this->once())
            ->method('getUser')
            ->willReturn(null);

        $this->assertTrue($this->service->supports($request));
    }

    public function testSupportsDifferentUser(): void
    {
        $request = new Request();
        $request->query->set(MessageController::MESSAGE_ID_PARAMETER_NAME, $messageId = uniqid('message-id'));

        $this->security
            ->expects($this->once())
            ->method('getUser')
            ->willReturn($this->createMock(UserInterface::class));

        $this->transport
            ->expects($this->once())
            ->method('find')
            ->with($messageId)
            ->willReturn(new Envelope(new AutoConnect($userId = uniqid('user-id-'))));

        $this->userProvider
            ->expects($this->once())
            ->method('loadUserByIdentifier')
            ->with($userId)
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
        $request->query->set(MessageController::MESSAGE_ID_PARAMETER_NAME, $messageId = uniqid('message-id'));

        $this->security
            ->expects($this->once())
            ->method('getUser')
            ->willReturn($user = $this->createMock(UserInterface::class));

        $this->transport
            ->expects($this->once())
            ->method('find')
            ->with($messageId)
            ->willReturn(new Envelope(new AutoConnect($userId = uniqid('user-id-'))));

        $this->userProvider
            ->expects($this->once())
            ->method('loadUserByIdentifier')
            ->with($userId)
            ->willReturn($user);

        $this->assertFalse($this->service->supports($request));
    }

    public function testAuthenticateNoMessage(): void
    {
        $request = new Request();
        $request->query->set(MessageController::MESSAGE_ID_PARAMETER_NAME, $messageId = uniqid('message-id'));

        $this->transport
            ->expects($this->once())
            ->method('find')
            ->with($messageId)
            ->willReturn(null);

        $this->expectException(CustomUserMessageAuthenticationException::class);
        $this->expectExceptionMessage('Invalid message id.');

        $this->service->authenticate($request);
    }

    public function testAuthenticate(): void
    {
        $request = new Request();
        $request->query->set(MessageController::MESSAGE_ID_PARAMETER_NAME, $messageId = uniqid('message-id'));

        $this->transport
            ->expects($this->once())
            ->method('find')
            ->with($messageId)
            ->willReturn(new Envelope(new AutoConnect($userId = uniqid('user-id-'))));

        $user = $this->createMockWithExtractMethods(
            UserInterface::class,
            ['getUserIdentifier']
        );

        $user
            ->expects($this->once())
            ->method('getUserIdentifier')
            ->willReturn($userId);

        $this->userProvider
            ->expects($this->once())
            ->method('loadUserByIdentifier')
            ->with($userId)
            ->willReturn($user);

        $passport = $this->service->authenticate($request);

        $this->assertInstanceOf(
            SelfValidatingPassport::class,
            $passport
        );

        $userBadge = $passport->getBadge(UserBadge::class);

        $this->assertSame(
            $userId.'+message-'.$messageId,
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
}

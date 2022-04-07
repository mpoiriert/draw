<?php

namespace Draw\Component\Security\Tests\Http\Authenticator;

use DateTimeImmutable;
use Draw\Component\Core\Reflection\ReflectionAccessor;
use Draw\Component\Security\Http\Authenticator\JwtAuthenticator;
use Draw\Component\Security\Http\Authenticator\Passport\Badge\JwtPayloadBadge;
use Draw\Component\Security\Jwt\JwtEncoder;
use Draw\Component\Tester\MockBuilderTrait;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Core\Exception\UserNotFoundException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Http\Authenticator\AbstractAuthenticator;
use Symfony\Component\Security\Http\Authenticator\AuthenticatorInterface;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;
use Symfony\Component\Security\Http\Authenticator\Token\PostAuthenticationToken;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @covers \Draw\Component\Security\Http\Authenticator\JwtAuthenticator
 */
class JwtAuthenticatorTest extends TestCase
{
    use MockBuilderTrait;

    private JwtAuthenticator $service;

    private JwtEncoder $jwtEncoder;

    private UserProviderInterface $userProvider;

    private string $userIdentifierPayloadKey;

    private string $userIdentifierGetter;

    private TranslatorInterface $translator;

    public function setUp(): void
    {
        $this->userProvider = $this->createMockWithExtractMethods(
            UserProviderInterface::class,
            ['loadUserByIdentifier']
        );

        $this->service = new JwtAuthenticator(
            $this->jwtEncoder = $this->createMock(JwtEncoder::class),
            $this->userProvider,
            $this->userIdentifierPayloadKey = uniqid('key'),
            $this->userIdentifierGetter = uniqid('get'),
            $this->translator = $this->createMock(TranslatorInterface::class)
        );
    }

    public function testConstruct(): void
    {
        $this->assertInstanceOf(
            AuthenticatorInterface::class,
            $this->service
        );
    }

    public function testSupports(): void
    {
        $request = new Request();
        $request->headers->set('Authorization', 'Bearer '.uniqid('jwt-'));

        $this->assertTrue($this->service->supports($request));
    }

    public function testSupportsNoToken(): void
    {
        $this->assertFalse($this->service->supports(new Request()));
    }

    public function testGenerateToken(): void
    {
        $user = $this->createMockWithExtractMethods(
            UserInterface::class,
            [$this->userIdentifierGetter]
        );

        $user
            ->expects($this->once())
            ->method($this->userIdentifierGetter)
            ->willReturn($userId = uniqid('id'));

        $this->jwtEncoder
            ->expects($this->once())
            ->method('encode')
            ->with([$this->userIdentifierPayloadKey => $userId], null)
            ->willReturn($token = uniqid('token-'));

        $this->assertSame(
            $token,
            $this->service->generaToken(
                $user,
                null
            )
        );
    }

    public function testGenerateTokenWithExpiration(): void
    {
        $user = $this->createMockWithExtractMethods(
            UserInterface::class,
            [$this->userIdentifierGetter]
        );

        $user
            ->expects($this->once())
            ->method($this->userIdentifierGetter)
            ->willReturn($userId = uniqid('id'));

        $this->jwtEncoder
            ->expects($this->once())
            ->method('encode')
            ->with(
                [$this->userIdentifierPayloadKey => $userId],
                $this->equalToWithDelta(new DateTimeImmutable('+ 7 days'), 1)
            )
            ->willReturn($token = uniqid('token-'));

        $this->assertSame(
            $token,
            $this->service->generaToken(
                $user
            )
        );
    }

    public function testGenerateTokenWithExtraPayload(): void
    {
        $user = $this->createMockWithExtractMethods(
            UserInterface::class,
            [$this->userIdentifierGetter]
        );

        $user
            ->expects($this->once())
            ->method($this->userIdentifierGetter)
            ->willReturn($userId = uniqid('id'));

        $extraPayload = [
            'extra-data' => uniqid('value-'),
        ];

        $this->jwtEncoder
            ->expects($this->once())
            ->method('encode')
            ->with(
                [$this->userIdentifierPayloadKey => $userId] + $extraPayload,
            )
            ->willReturn($token = uniqid('token-'));

        $this->assertSame(
            $token,
            $this->service->generaToken(
                $user,
                null,
                $extraPayload
            )
        );
    }

    public function testAuthenticate(): void
    {
        $request = new Request();
        $request->headers->set('Authorization', 'Bearer '.$token = uniqid('jwt-'));

        $this->jwtEncoder
            ->expects($this->once())
            ->method('decode')
            ->with($token)
            ->willReturn((object) [$this->userIdentifierPayloadKey => $userId = uniqid('id-')]);

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
            $userId.'+jwt-token',
            $userBadge->getUserIdentifier()
        );

        $this->assertSame(
            $user,
            $userBadge->getUser()
        );
    }

    public function testAuthenticateWithExtraPayload(): void
    {
        $request = new Request();
        $request->headers->set('Authorization', 'Bearer '.$token = uniqid('jwt-'));

        $this->jwtEncoder
            ->expects($this->once())
            ->method('decode')
            ->with($token)
            ->willReturn(
                (object) [
                    $this->userIdentifierPayloadKey => $userId = uniqid('id-'),
                    $extraKey = uniqid('extra-key-') => $extraValue = uniqid('extra-value-'),
                ]
            );

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

        $jwtPayloadBadge = $passport->getBadge(JwtPayloadBadge::class);

        $this->assertSame(
            $extraValue,
            $jwtPayloadBadge->getPayloadKeyValue($extraKey)
        );

        foreach (['nbf', 'iat', 'exp', $this->userIdentifierPayloadKey] as $key) {
            $this->assertNull($jwtPayloadBadge->getPayloadKeyValue($key));
        }
    }

    public function testAuthenticateUserNotFound(): void
    {
        $request = new Request();
        $request->headers->set('Authorization', 'Bearer '.$token = uniqid('jwt-'));

        $this->jwtEncoder
            ->expects($this->once())
            ->method('decode')
            ->with($token)
            ->willReturn((object) []);

        $this->expectException(UserNotFoundException::class);
        $this->expectExceptionMessage('Token attribute ['.$this->userIdentifierPayloadKey.'] not found');

        $this->service->authenticate($request);
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
        $this->translator
            ->expects($this->once())
            ->method('trans')
            ->with(
                $message = uniqid('message-'),
                $messageData = ['data' => uniqid()],
                'security'
            )
            ->willReturn($translatedMessage = uniqid('translated-'));

        $this->expectException(HttpException::class);
        $this->expectExceptionMessage($translatedMessage);

        try {
            $this->service->onAuthenticationFailure(
                new Request(),
                new CustomUserMessageAuthenticationException(
                    $message,
                    $messageData
                )
            );
            $this->fail('Expected Exception');
        } catch (HttpException $error) {
            $this->assertSame(
                Response::HTTP_FORBIDDEN,
                $error->getStatusCode()
            );

            throw $error;
        }
    }

    public function testOnAuthenticationFailureNoTranslator(): void
    {
        $this->translator
            ->expects($this->never())
            ->method('trans');

        ReflectionAccessor::setPropertyValue(
            $this->service,
            'translator',
            null
        );

        $message = uniqid('message-key-');
        $messageData = ['key' => uniqid()];

        $translatedMessage = strtr($message, $messageData);

        $this->expectException(HttpException::class);
        $this->expectExceptionMessage($translatedMessage);

        try {
            $this->service->onAuthenticationFailure(
                new Request(),
                new CustomUserMessageAuthenticationException(
                    $message,
                    $messageData
                )
            );
            $this->fail('Expected Exception');
        } catch (HttpException $error) {
            $this->assertSame(
                Response::HTTP_FORBIDDEN,
                $error->getStatusCode()
            );

            throw $error;
        }
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

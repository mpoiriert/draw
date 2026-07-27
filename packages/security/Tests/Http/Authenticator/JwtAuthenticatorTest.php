<?php

namespace Draw\Component\Security\Tests\Http\Authenticator;

use Draw\Component\Core\Reflection\ReflectionAccessor;
use Draw\Component\Security\Http\Authenticator\JwtAuthenticator;
use Draw\Component\Security\Http\Authenticator\Passport\Badge\JwtPayloadBadge;
use Draw\Component\Security\Jwt\JwtEncoder;
use Draw\Component\Security\Tests\Stub\JwtAuthenticatableUserInterface;
use Draw\Component\Tester\MockTrait;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
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
 * @internal
 */
#[CoversClass(JwtAuthenticator::class)]
class JwtAuthenticatorTest extends TestCase
{
    use MockTrait;

    private JwtAuthenticator $object;

    private JwtEncoder&MockObject $jwtEncoder;

    private UserProviderInterface&MockObject $userProvider;

    private string $userIdentifierPayloadKey;

    private TranslatorInterface&MockObject $translator;

    private static string $userIdentifierGetter = 'getJwtIdentifier';

    protected function setUp(): void
    {
        $this->userProvider = $this->createMock(UserProviderInterface::class);

        $this->object = new JwtAuthenticator(
            $this->jwtEncoder = $this->createMock(JwtEncoder::class),
            $this->userProvider,
            $this->userIdentifierPayloadKey = uniqid('key'),
            self::$userIdentifierGetter,
            $this->translator = $this->createMock(TranslatorInterface::class)
        );
    }

    public function testConstruct(): void
    {
        $this->assertInstanceOf(
            AuthenticatorInterface::class,
            $this->object
        );
    }

    public function testSupports(): void
    {
        $request = new Request();
        $request->headers->set('Authorization', 'Bearer '.uniqid('jwt-'));

        $this->assertTrue($this->object->supports($request));
    }

    public function testSupportsInvalidToken(): void
    {
        $request = new Request();
        $request->headers->set('Authorization', 'Bearer '.uniqid('jwt-'));

        $this->jwtEncoder
            ->expects($this->once())
            ->method('decode')
            ->willThrowException(new \UnexpectedValueException())
        ;

        $this->assertFalse($this->object->supports($request));
    }

    public function testSupportsNoToken(): void
    {
        $this->assertFalse($this->object->supports(new Request()));
    }

    public function testGenerateToken(): void
    {
        $user = $this->createMock(JwtAuthenticatableUserInterface::class);

        $user
            ->expects($this->once())
            ->method(self::$userIdentifierGetter)
            ->willReturn($userId = uniqid('id'))
        ;

        $this->jwtEncoder
            ->expects($this->once())
            ->method('encode')
            ->with([$this->userIdentifierPayloadKey => $userId], null)
            ->willReturn($token = uniqid('token-'))
        ;

        $this->assertSame(
            $token,
            $this->object->generaToken(
                $user,
                0
            )
        );
    }

    public function testGenerateTokenDefaultNull(): void
    {
        ReflectionAccessor::setPropertyValue(
            $this->object,
            'expiration',
            null
        );

        $user = $this->createMock(JwtAuthenticatableUserInterface::class);

        $user
            ->expects($this->once())
            ->method(self::$userIdentifierGetter)
            ->willReturn($userId = uniqid('id'))
        ;

        $this->jwtEncoder
            ->expects($this->once())
            ->method('encode')
            ->with([$this->userIdentifierPayloadKey => $userId], null)
            ->willReturn($token = uniqid('token-'))
        ;

        $this->assertSame(
            $token,
            $this->object->generaToken(
                $user
            )
        );
    }

    public function testGenerateTokenWithExpiration(): void
    {
        $user = $this->createMock(JwtAuthenticatableUserInterface::class);

        $user
            ->expects($this->once())
            ->method(self::$userIdentifierGetter)
            ->willReturn($userId = uniqid('id'))
        ;

        $this->jwtEncoder
            ->expects($this->once())
            ->method('encode')
            ->with(
                [$this->userIdentifierPayloadKey => $userId],
                $this->equalToWithDelta(new \DateTimeImmutable('+ 7 days'), 1)
            )
            ->willReturn($token = uniqid('token-'))
        ;

        $this->assertSame(
            $token,
            $this->object->generaToken(
                $user
            )
        );
    }

    public function testGenerateTokenWithExtraPayload(): void
    {
        $user = $this->createMock(JwtAuthenticatableUserInterface::class);

        $user
            ->expects($this->once())
            ->method(self::$userIdentifierGetter)
            ->willReturn($userId = uniqid('id'))
        ;

        $extraPayload = [
            'extra-data' => uniqid('value-'),
        ];

        $this->jwtEncoder
            ->expects($this->once())
            ->method('encode')
            ->with(
                [$this->userIdentifierPayloadKey => $userId] + $extraPayload,
            )
            ->willReturn($token = uniqid('token-'))
        ;

        $this->assertSame(
            $token,
            $this->object->generaToken(
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
            ->expects($this->any())
            ->method('decode')
            ->with($token)
            ->willReturn((object) [$this->userIdentifierPayloadKey => $userId = uniqid('id-')])
        ;

        $user = $this->createMock(UserInterface::class);

        $user
            ->expects($this->once())
            ->method('getUserIdentifier')
            ->willReturn($userId)
        ;

        $this->userProvider
            ->expects($this->once())
            ->method('loadUserByIdentifier')
            ->with($userId)
            ->willReturn($user)
        ;

        $passport = $this->object->authenticate($request);

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
            ->expects($this->any())
            ->method('decode')
            ->with($token)
            ->willReturn(
                (object) [
                    $this->userIdentifierPayloadKey => $userId = uniqid('id-'),
                    $extraKey = uniqid('extra-key-') => $extraValue = uniqid('extra-value-'),
                ]
            )
        ;

        $user = $this->createMock(UserInterface::class);

        $user
            ->expects($this->once())
            ->method('getUserIdentifier')
            ->willReturn($userId)
        ;

        $this->userProvider
            ->expects($this->once())
            ->method('loadUserByIdentifier')
            ->with($userId)
            ->willReturn($user)
        ;

        $passport = $this->object->authenticate($request);

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
            ->expects($this->any())
            ->method('decode')
            ->with($token)
            ->willReturn((object) [])
        ;

        $this->expectException(UserNotFoundException::class);
        $this->expectExceptionMessage('Token attribute ['.$this->userIdentifierPayloadKey.'] not found');

        $this->object->authenticate($request);
    }

    public function testAuthenticateInvalidPayload(): void
    {
        $request = new Request();
        $request->headers->set('Authorization', 'Bearer '.$token = uniqid('jwt-'));

        $this->jwtEncoder
            ->expects($this->any())
            ->method('decode')
            ->with($token)
            ->willThrowException(new \UnexpectedValueException())
        ;

        $this->expectException(\UnexpectedValueException::class);

        $this->object->authenticate($request);
    }

    public function testOnAuthenticationSuccess(): void
    {
        $this->assertNull(
            $this->object->onAuthenticationSuccess(
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
            ->willReturn($translatedMessage = uniqid('translated-'))
        ;

        $this->expectException(HttpException::class);
        $this->expectExceptionMessage($translatedMessage);

        $previous = new CustomUserMessageAuthenticationException(
            $message,
            $messageData
        );

        try {
            $this->object->onAuthenticationFailure(
                new Request(),
                $previous
            );
        } catch (HttpException $error) {
            $this->assertSame(
                Response::HTTP_FORBIDDEN,
                $error->getStatusCode()
            );

            $this->assertSame(
                $previous,
                $error->getPrevious()
            );

            throw $error;
        }
    }

    public function testOnAuthenticationFailureNoTranslator(): void
    {
        $this->translator
            ->expects($this->never())
            ->method('trans')
        ;

        ReflectionAccessor::setPropertyValue(
            $this->object,
            'translator',
            null
        );

        $message = uniqid('message-key-');
        $messageData = ['key' => uniqid()];

        $translatedMessage = strtr($message, $messageData);

        $this->expectException(HttpException::class);
        $this->expectExceptionMessage($translatedMessage);

        try {
            $this->object->onAuthenticationFailure(
                new Request(),
                new CustomUserMessageAuthenticationException(
                    $message,
                    $messageData
                )
            );
        } catch (HttpException $error) {
            $this->assertSame(
                Response::HTTP_FORBIDDEN,
                $error->getStatusCode()
            );

            throw $error;
        }
    }

    /**
     * This is form the parent abstract class but, we test it as part of a contract test.
     *
     * @see AbstractAuthenticator
     */
    public function testCreateToken(): void
    {
        $passport = $this->createMock(Passport::class);
        $passport
            ->expects($this->any())
            ->method('getUser')
            ->willReturn($user = $this->createMock(UserInterface::class))
        ;

        $user
            ->expects($this->any())
            ->method('getRoles')
            ->willReturn($roles = [uniqid('ROLE_')])
        ;

        $token = $this->object->createToken(
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

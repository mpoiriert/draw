<?php

namespace Draw\Component\Security\Tests\Http\Authenticator;

use Draw\Component\Core\Reflection\ReflectionAccessor;
use Draw\Component\Security\Http\Authenticator\JwtAuthenticator;
use Draw\Component\Security\Http\Authenticator\Passport\Badge\JwtPayloadBadge;
use Draw\Component\Security\Jwt\JwtEncoder;
use Draw\Component\Security\Tests\Stub\JwtAuthenticatableUserInterface;
use PHPUnit\Framework\Attributes\CoversClass;
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
    private string $userIdentifierPayloadKey;

    private static string $userIdentifierGetter = 'getJwtIdentifier';

    protected function setUp(): void
    {
        $this->userIdentifierPayloadKey = uniqid('key');
    }

    public function testSupports(): void
    {
        $object = new JwtAuthenticator(
            static::createStub(JwtEncoder::class),
            static::createStub(UserProviderInterface::class),
            $this->userIdentifierPayloadKey,
            self::$userIdentifierGetter,
            static::createStub(TranslatorInterface::class)
        );

        $request = new Request();
        $request->headers->set('Authorization', 'Bearer '.uniqid('jwt-'));

        static::assertTrue($object->supports($request));
    }

    public function testSupportsInvalidToken(): void
    {
        $object = new JwtAuthenticator(
            $jwtEncoder = $this->createMock(JwtEncoder::class),
            static::createStub(UserProviderInterface::class),
            $this->userIdentifierPayloadKey,
            self::$userIdentifierGetter,
            static::createStub(TranslatorInterface::class)
        );

        $request = new Request();
        $request->headers->set('Authorization', 'Bearer '.uniqid('jwt-'));

        $jwtEncoder
            ->expects(static::once())
            ->method('decode')
            ->willThrowException(new \UnexpectedValueException())
            ->seal()
        ;

        static::assertFalse($object->supports($request));
    }

    public function testSupportsNoToken(): void
    {
        $object = new JwtAuthenticator(
            static::createStub(JwtEncoder::class),
            static::createStub(UserProviderInterface::class),
            $this->userIdentifierPayloadKey,
            self::$userIdentifierGetter,
            static::createStub(TranslatorInterface::class)
        );

        static::assertFalse($object->supports(new Request()));
    }

    public function testGenerateToken(): void
    {
        $object = new JwtAuthenticator(
            $jwtEncoder = $this->createMock(JwtEncoder::class),
            static::createStub(UserProviderInterface::class),
            $this->userIdentifierPayloadKey,
            self::$userIdentifierGetter,
            static::createStub(TranslatorInterface::class)
        );

        $user = $this->createMock(JwtAuthenticatableUserInterface::class);

        $user
            ->expects(static::once())
            ->method(self::$userIdentifierGetter)
            ->willReturn($userId = uniqid('id'))
            ->seal()
        ;

        $jwtEncoder
            ->expects(static::once())
            ->method('encode')
            ->with([$this->userIdentifierPayloadKey => $userId], null)
            ->willReturn($token = uniqid('token-'))
            ->seal()
        ;

        static::assertSame(
            $token,
            $object->generaToken(
                $user,
                0
            )
        );
    }

    public function testGenerateTokenDefaultNull(): void
    {
        $object = new JwtAuthenticator(
            $jwtEncoder = $this->createMock(JwtEncoder::class),
            static::createStub(UserProviderInterface::class),
            $this->userIdentifierPayloadKey,
            self::$userIdentifierGetter,
            static::createStub(TranslatorInterface::class),
            null
        );

        $user = $this->createMock(JwtAuthenticatableUserInterface::class);

        $user
            ->expects(static::once())
            ->method(self::$userIdentifierGetter)
            ->willReturn($userId = uniqid('id'))
            ->seal()
        ;

        $jwtEncoder
            ->expects(static::once())
            ->method('encode')
            ->with([$this->userIdentifierPayloadKey => $userId], null)
            ->willReturn($token = uniqid('token-'))
            ->seal()
        ;

        static::assertSame(
            $token,
            $object->generaToken(
                $user
            )
        );
    }

    public function testGenerateTokenWithExpiration(): void
    {
        $object = new JwtAuthenticator(
            $jwtEncoder = $this->createMock(JwtEncoder::class),
            static::createStub(UserProviderInterface::class),
            $this->userIdentifierPayloadKey,
            self::$userIdentifierGetter,
            static::createStub(TranslatorInterface::class)
        );

        $user = $this->createMock(JwtAuthenticatableUserInterface::class);

        $user
            ->expects(static::once())
            ->method(self::$userIdentifierGetter)
            ->willReturn($userId = uniqid('id'))
            ->seal()
        ;

        $jwtEncoder
            ->expects(static::atLeastOnce())
            ->method('encode')
            ->with(
                [$this->userIdentifierPayloadKey => $userId],
                static::equalToWithDelta(new \DateTimeImmutable('+ 7 days'), 1)
            )
            ->willReturn($token = uniqid('token-'))
            ->seal()
        ;

        static::assertSame(
            $token,
            $object->generaToken(
                $user
            )
        );
    }

    public function testGenerateTokenWithExtraPayload(): void
    {
        $object = new JwtAuthenticator(
            $jwtEncoder = $this->createMock(JwtEncoder::class),
            static::createStub(UserProviderInterface::class),
            $this->userIdentifierPayloadKey,
            self::$userIdentifierGetter,
            static::createStub(TranslatorInterface::class),
            null
        );

        $user = $this->createMock(JwtAuthenticatableUserInterface::class);

        $user
            ->expects(static::once())
            ->method(self::$userIdentifierGetter)
            ->willReturn($userId = uniqid('id'))
            ->seal()
        ;

        $extraPayload = [
            'extra-data' => uniqid('value-'),
        ];

        $jwtEncoder
            ->expects(static::once())
            ->method('encode')
            ->with(
                [$this->userIdentifierPayloadKey => $userId] + $extraPayload,
            )
            ->willReturn($token = uniqid('token-'))
            ->seal()
        ;

        static::assertSame(
            $token,
            $object->generaToken(
                $user,
                null,
                $extraPayload
            )
        );
    }

    public function testAuthenticate(): void
    {
        $object = new JwtAuthenticator(
            $jwtEncoder = $this->createMock(JwtEncoder::class),
            $userProvider = static::createMock(UserProviderInterface::class),
            $this->userIdentifierPayloadKey,
            self::$userIdentifierGetter,
            static::createStub(TranslatorInterface::class),
            null
        );

        $request = new Request();
        $request->headers->set('Authorization', 'Bearer '.$token = uniqid('jwt-'));

        $jwtEncoder
            ->expects(static::atLeastOnce())
            ->method('decode')
            ->with($token)
            ->willReturn((object) [$this->userIdentifierPayloadKey => $userId = uniqid('id-')])
            ->seal()
        ;

        $user = $this->createMock(UserInterface::class);

        $user
            ->expects(static::once())
            ->method('getUserIdentifier')
            ->willReturn($userId)
            ->seal()
        ;

        $userProvider
            ->expects(static::once())
            ->method('loadUserByIdentifier')
            ->with($userId)
            ->willReturn($user)
            ->seal()
        ;

        $passport = $object->authenticate($request);

        static::assertInstanceOf(
            SelfValidatingPassport::class,
            $passport
        );

        $userBadge = $passport->getBadge(UserBadge::class);

        static::assertSame(
            $userId.'+jwt-token',
            $userBadge->getUserIdentifier()
        );

        static::assertSame(
            $user,
            $userBadge->getUser()
        );
    }

    public function testAuthenticateWithExtraPayload(): void
    {
        $object = new JwtAuthenticator(
            $jwtEncoder = $this->createMock(JwtEncoder::class),
            $userProvider = static::createMock(UserProviderInterface::class),
            $this->userIdentifierPayloadKey,
            self::$userIdentifierGetter,
            static::createStub(TranslatorInterface::class),
            null
        );

        $request = new Request();
        $request->headers->set('Authorization', 'Bearer '.$token = uniqid('jwt-'));

        $jwtEncoder
            ->expects(static::atLeastOnce())
            ->method('decode')
            ->with($token)
            ->willReturn(
                (object) [
                    $this->userIdentifierPayloadKey => $userId = uniqid('id-'),
                    $extraKey = uniqid('extra-key-') => $extraValue = uniqid('extra-value-'),
                ]
            )
            ->seal()
        ;

        $user = $this->createMock(UserInterface::class);

        $user
            ->expects(static::once())
            ->method('getUserIdentifier')
            ->willReturn($userId)
            ->seal()
        ;

        $userProvider
            ->expects(static::once())
            ->method('loadUserByIdentifier')
            ->with($userId)
            ->willReturn($user)
            ->seal()
        ;

        $passport = $object->authenticate($request);

        $jwtPayloadBadge = $passport->getBadge(JwtPayloadBadge::class);

        static::assertSame(
            $extraValue,
            $jwtPayloadBadge->getPayloadKeyValue($extraKey)
        );

        foreach (['nbf', 'iat', 'exp', $this->userIdentifierPayloadKey] as $key) {
            static::assertNull($jwtPayloadBadge->getPayloadKeyValue($key));
        }
    }

    public function testAuthenticateUserNotFound(): void
    {
        $object = new JwtAuthenticator(
            $jwtEncoder = $this->createMock(JwtEncoder::class),
            static::createStub(UserProviderInterface::class),
            $this->userIdentifierPayloadKey,
            self::$userIdentifierGetter,
            static::createStub(TranslatorInterface::class),
            null
        );

        $request = new Request();
        $request->headers->set('Authorization', 'Bearer '.$token = uniqid('jwt-'));

        $jwtEncoder
            ->expects(static::atLeastOnce())
            ->method('decode')
            ->with($token)
            ->willReturn((object) [])
            ->seal()
        ;

        $this->expectException(UserNotFoundException::class);
        $this->expectExceptionMessage('Token attribute ['.$this->userIdentifierPayloadKey.'] not found');

        $object->authenticate($request);
    }

    public function testAuthenticateInvalidPayload(): void
    {
        $object = new JwtAuthenticator(
            $jwtEncoder = $this->createMock(JwtEncoder::class),
            static::createStub(UserProviderInterface::class),
            $this->userIdentifierPayloadKey,
            self::$userIdentifierGetter,
            static::createStub(TranslatorInterface::class),
            null
        );

        $request = new Request();
        $request->headers->set('Authorization', 'Bearer '.$token = uniqid('jwt-'));

        $jwtEncoder
            ->expects(static::atLeastOnce())
            ->method('decode')
            ->with($token)
            ->willThrowException(new \UnexpectedValueException())
            ->seal()
        ;

        $this->expectException(\UnexpectedValueException::class);

        $object->authenticate($request);
    }

    public function testOnAuthenticationSuccess(): void
    {
        $object = new JwtAuthenticator(
            static::createStub(JwtEncoder::class),
            static::createStub(UserProviderInterface::class),
            $this->userIdentifierPayloadKey,
            self::$userIdentifierGetter,
            static::createStub(TranslatorInterface::class),
            null
        );

        static::assertNull(
            $object->onAuthenticationSuccess(
                new Request(),
                static::createStub(TokenInterface::class),
                uniqid('firewall-')
            )
        );
    }

    public function testOnAuthenticationFailure(): void
    {
        $object = new JwtAuthenticator(
            static::createStub(JwtEncoder::class),
            static::createStub(UserProviderInterface::class),
            $this->userIdentifierPayloadKey,
            self::$userIdentifierGetter,
            $translator = static::createMock(TranslatorInterface::class),
            null
        );

        $translator
            ->expects(static::once())
            ->method('trans')
            ->with(
                $message = uniqid('message-'),
                $messageData = ['data' => uniqid()],
                'security'
            )
            ->willReturn($translatedMessage = uniqid('translated-'))
            ->seal()
        ;

        $this->expectException(HttpException::class);
        $this->expectExceptionMessage($translatedMessage);

        $previous = new CustomUserMessageAuthenticationException(
            $message,
            $messageData
        );

        try {
            $object->onAuthenticationFailure(
                new Request(),
                $previous
            );
        } catch (HttpException $error) {
            static::assertSame(
                Response::HTTP_FORBIDDEN,
                $error->getStatusCode()
            );

            static::assertSame(
                $previous,
                $error->getPrevious()
            );

            throw $error;
        }
    }

    public function testOnAuthenticationFailureNoTranslator(): void
    {
        $object = new JwtAuthenticator(
            static::createStub(JwtEncoder::class),
            static::createStub(UserProviderInterface::class),
            $this->userIdentifierPayloadKey,
            self::$userIdentifierGetter,
            $translator = static::createMock(TranslatorInterface::class),
            null
        );

        $translator
            ->expects(static::never())
            ->method('trans')
            ->seal()
        ;

        ReflectionAccessor::setPropertyValue(
            $object,
            'translator',
            null
        );

        $message = uniqid('message-key-');
        $messageData = ['key' => uniqid()];

        $translatedMessage = strtr($message, $messageData);

        $this->expectException(HttpException::class);
        $this->expectExceptionMessage($translatedMessage);

        try {
            $object->onAuthenticationFailure(
                new Request(),
                new CustomUserMessageAuthenticationException(
                    $message,
                    $messageData
                )
            );
        } catch (HttpException $error) {
            static::assertSame(
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
        $object = new JwtAuthenticator(
            static::createStub(JwtEncoder::class),
            static::createStub(UserProviderInterface::class),
            $this->userIdentifierPayloadKey,
            self::$userIdentifierGetter,
            static::createStub(TranslatorInterface::class),
            null
        );

        $passport = static::createStub(Passport::class);
        $passport
            ->method('getUser')
            ->willReturn($user = static::createStub(UserInterface::class))
            ->seal()
        ;

        $user
            ->method('getRoles')
            ->willReturn($roles = [uniqid('ROLE_')])
            ->seal()
        ;

        $token = $object->createToken(
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
}

<?php

namespace Draw\Component\Security\Http\Authenticator;

use DateTimeImmutable;
use Draw\Component\Security\Http\Authenticator\Passport\Badge\JwtPayloadBadge;
use Draw\Component\Security\Jwt\JwtEncoder;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\UserNotFoundException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Http\Authenticator\AbstractAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;
use Symfony\Contracts\Translation\TranslatorInterface;

class JwtAuthenticator extends AbstractAuthenticator
{
    private UserProviderInterface $userProvider;

    private JwtEncoder $encoder;

    private string $userIdentifierPayloadKey;

    private string $userIdentifierGetter;

    private ?TranslatorInterface $translator;

    public function __construct(
        JwtEncoder $encoder,
        UserProviderInterface $userProvider,
        string $userIdentifierPayloadKey,
        string $userIdentifierGetter,
        ?TranslatorInterface $translator = null
    ) {
        $this->encoder = $encoder;
        $this->userProvider = $userProvider;
        $this->userIdentifierPayloadKey = $userIdentifierPayloadKey;
        $this->userIdentifierGetter = $userIdentifierGetter;
        $this->translator = $translator;
    }

    public function supports(Request $request): ?bool
    {
        return null !== $this->getToken($request);
    }

    public function getEncoder(): JwtEncoder
    {
        return $this->encoder;
    }

    public function generaToken(UserInterface $user, ?string $expiration = '+ 7 days', array $extraPayload = []): string
    {
        return $this->encoder->encode(
            array_merge(
                [$this->userIdentifierPayloadKey => call_user_func([$user, $this->userIdentifierGetter])],
                $extraPayload
            ),
            $expiration ? new DateTimeImmutable($expiration) : null
        );
    }

    public function getUserFromToken(string $token): UserInterface
    {
        return $this->getUserFromPayload($this->encoder->decode($token));
    }

    private function getUserFromPayload(object $payload): UserInterface
    {
        $userId = $payload->{$this->userIdentifierPayloadKey} ?? null;

        if (null === $userId) {
            throw new UserNotFoundException('Token attribute ['.$this->userIdentifierPayloadKey.'] not found');
        }

        return $this->userProvider->loadUserByIdentifier($userId);
    }

    public function getToken(Request $request): ?string
    {
        if ($request->headers->has('Authorization')) {
            if (preg_match('/Bearer\s(\S+)/', $request->headers->get('Authorization'), $matches)) {
                return $matches[1];
            }
        }

        return null;
    }

    public function authenticate(Request $request): Passport
    {
        $token = $this->getToken($request);
        $payload = $this->encoder->decode($token);
        $user = $this->getUserFromPayload($payload);

        $badges = [];
        if ($badge = JwtPayloadBadge::createIfNeeded((array) $payload, [$this->userIdentifierPayloadKey])) {
            $badges[] = $badge;
        }

        return new SelfValidatingPassport(
            new UserBadge($user->getUserIdentifier().'+jwt-token', function () use ($user) {
                return $user;
            }),
            $badges
        );
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        return null;
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): ?Response
    {
        throw new HttpException(Response::HTTP_FORBIDDEN, $this->translate($exception->getMessageKey(), $exception->getMessageData()));
    }

    private function translate($message, array $data = []): string
    {
        if (!$this->translator) {
            return strtr($message, $data);
        }

        return $this->translator->trans($message, $data, 'security');
    }
}

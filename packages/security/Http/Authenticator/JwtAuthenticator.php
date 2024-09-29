<?php

namespace Draw\Component\Security\Http\Authenticator;

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
    public function __construct(
        private JwtEncoder $encoder,
        private UserProviderInterface $userProvider,
        private string $userIdentifierPayloadKey,
        private string $userIdentifierGetter,
        private ?TranslatorInterface $translator = null,
        private ?string $expiration = '+ 7 days',
    ) {
    }

    public function supports(Request $request): ?bool
    {
        return null !== $this->getToken($request);
    }

    public function getEncoder(): JwtEncoder
    {
        return $this->encoder;
    }

    public function generaToken(UserInterface $user, int|string|null $expiration = null, array $extraPayload = []): string
    {
        $expiration ??= $this->expiration;

        if (0 === $expiration || null === $expiration) {
            $expiration = null;
        } elseif (\is_int($expiration)) {
            $expiration = (new \DateTimeImmutable())->setTimestamp($expiration);
        } else {
            $expiration = new \DateTimeImmutable($expiration);
        }

        return $this->encoder->encode(
            array_merge(
                [$this->userIdentifierPayloadKey => \call_user_func([$user, $this->userIdentifierGetter])],
                $extraPayload
            ),
            $expiration
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
        if (!$request->headers->has('Authorization')) {
            return null;
        }
        if (!preg_match('/Bearer\s(\S+)/', $request->headers->get('Authorization'), $matches)) {
            return null;
        }

        $token = $matches[1];

        try {
            $this->encoder->decode($token);

            return $token;
        } catch (\UnexpectedValueException) {
            return null;
        }
    }

    public function authenticate(Request $request): Passport
    {
        $token = $this->getToken($request);

        if (null === $token) {
            throw new \UnexpectedValueException('Request does not contains valid token');
        }

        $payload = $this->encoder->decode($token);
        $user = $this->getUserFromPayload($payload);

        $badges = [];
        if ($badge = JwtPayloadBadge::createIfNeeded((array) $payload, [$this->userIdentifierPayloadKey])) {
            $badges[] = $badge;
        }

        return new SelfValidatingPassport(
            new UserBadge($user->getUserIdentifier().'+jwt-token', static fn () => $user),
            $badges
        );
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        return null;
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): ?Response
    {
        throw new HttpException(Response::HTTP_FORBIDDEN, $this->translate($exception->getMessageKey(), $exception->getMessageData()), previous: $exception);
    }

    private function translate(string $message, array $data = []): string
    {
        if (!$this->translator) {
            return strtr($message, $data);
        }

        return $this->translator->trans($message, $data, 'security');
    }
}

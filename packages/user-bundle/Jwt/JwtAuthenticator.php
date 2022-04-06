<?php

namespace Draw\Bundle\UserBundle\Jwt;

use DateTimeImmutable;
use Draw\Bundle\UserBundle\Entity\SecurityUserInterface;
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

    private string $userIdPayloadKey;

    private ?TranslatorInterface $translator;

    public function __construct(
        JwtEncoder $encoder,
        UserProviderInterface $userProvider,
        string $userIdPayloadKey,
        ?TranslatorInterface $translator = null
    ) {
        $this->encoder = $encoder;
        $this->userProvider = $userProvider;
        $this->userIdPayloadKey = $userIdPayloadKey;
        $this->translator = $translator;
    }

    public function supports(Request $request): ?bool
    {
        return null !== $this->getToken($request);
    }

    public function generaToken(SecurityUserInterface $user, ?string $expiration = '+ 7 days'): string
    {
        return $this->encoder->encode(
            [$this->userIdPayloadKey => $user->getId()],
            $expiration ? new DateTimeImmutable($expiration) : null
        );
    }

    public function getUserFromToken(string $token): UserInterface
    {
        $userId = ((array) $this->encoder->decode($token))[$this->userIdPayloadKey] ?? null;

        if (null === $userId) {
            throw new UserNotFoundException('Token attribute ['.$this->userIdPayloadKey.'] not found');
        }

        return $this->userProvider->loadUserByIdentifier($userId);
    }

    private function getToken(Request $request): ?string
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
        $user = $this->getUserFromToken($this->getToken($request));

        return new SelfValidatingPassport(
            new UserBadge($user->getUserIdentifier().'+jwt-token', function () use ($user) {
                return $user;
            })
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

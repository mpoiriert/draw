<?php

namespace Draw\Bundle\UserBundle\Jwt;

use Firebase\JWT\JWT;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Guard\AbstractGuardAuthenticator;
use Symfony\Contracts\Translation\TranslatorInterface;

class JwtAuthenticator extends AbstractGuardAuthenticator
{
    private $algorithm = 'HS256';

    private $key;

    private $userProvider;

    private $queryParameters;

    private $translator;

    public function __construct(
        UserProviderInterface $userProvider,
        ?TranslatorInterface $translator,
        $key,
        $queryParameters = []
    ) {
        $this->userProvider = $userProvider;
        $this->translator = $translator;
        $this->key = $key;
        $this->queryParameters = $queryParameters;
    }

    public function supportQueryParameter()
    {
        return !empty($this->queryParameters);
    }

    public function getFirstSupportedQueryParameter(): ?string
    {
        return reset($this->queryParameters) ?: null;
    }

    public function supports(Request $request)
    {
        return null !== $this->getToken($request);
    }

    public function encode(UserInterface $user, $expiration = '+ 7 days'): string
    {
        return JWT::encode(
            [
                'id' => $user->getId(),
                'exp' => (new \DateTime($expiration))->getTimestamp(),
            ],
            $this->key,
            $this->algorithm
        );
    }

    private function getToken(Request $request): ?string
    {
        if ($request->headers->has('Authorization')) {
            if (preg_match('/Bearer\s(\S+)/', $request->headers->get('Authorization'), $matches)) {
                return $matches[1];
            }
        }

        foreach ($this->queryParameters as $queryParameter) {
            if (!$request->query->has($queryParameter)) {
                continue;
            }

            return $request->query->get($queryParameter);
        }

        return null;
    }

    public function getCredentials(Request $request)
    {
        return [
            'token' => $this->getToken($request),
        ];
    }

    public function getUser($credentials, UserProviderInterface $userProvider)
    {
        try {
            $data = $this->decode($credentials['token']);
        } catch (\Throwable $error) {
            throw new CustomUserMessageAuthenticationException($error->getMessage());
        }

        if (!isset($data->id)) {
            return null;
        }

        return $this->userProvider->loadUserByUsername($data->id);
    }

    public function decode($token)
    {
        return JWT::decode($token, $this->key, [$this->algorithm]);
    }

    public function checkCredentials($credentials, UserInterface $user)
    {
        return true;
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, $providerKey)
    {
        // on success, let the request continue
        return null;
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception)
    {
        throw new HttpException(Response::HTTP_FORBIDDEN, $this->translate($exception->getMessageKey(), $exception->getMessageData()));
    }

    private function translate($message, array $data = [])
    {
        if (!$this->translator) {
            return strtr($message, $data);
        }

        return $this->translator->trans($message, $data, 'security');
    }

    public function start(Request $request, AuthenticationException $authException = null)
    {
        $data = [
            'message' => 'Authentication Required',
        ];

        return new JsonResponse($data, Response::HTTP_UNAUTHORIZED);
    }

    public function supportsRememberMe()
    {
        return false;
    }
}

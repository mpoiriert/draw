<?php namespace Draw\Bundle\UserBundle\Jwt;

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

    private $translator;

    public function __construct(
        UserProviderInterface $userProvider,
        ?TranslatorInterface $translator,
        $key
    ) {
        $this->userProvider = $userProvider;
        $this->translator = $translator;
        $this->key = $key;
    }

    public function supports(Request $request)
    {
        return $this->getToken($request) !== null;
    }

    public function encode(UserInterface $user): string
    {
        return JWT::encode(
            [
                'id' => $user->getId(),
                'exp' => (new \DateTime('+ 7 days'))->getTimestamp()
            ],
            $this->key,
            $this->algorithm
        );
    }

    private function getToken(Request $request): ?string
    {
        if (!$request->headers->has('Authorization')) {
            return null;
        }

        if (!preg_match('/Bearer\s(\S+)/', $request->headers->get('Authorization'), $matches)) {
            return null;
        }

        return $matches[1];
    }

    public function getCredentials(Request $request)
    {
        return [
            'token' => $this->getToken($request)
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
        throw new HttpException(
            Response::HTTP_FORBIDDEN,
            $this->translate($exception->getMessageKey(), $exception->getMessageData())
        );
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
            'message' => 'Authentication Required'
        ];

        return new JsonResponse($data, Response::HTTP_UNAUTHORIZED);
    }

    public function supportsRememberMe()
    {
        return false;
    }
}
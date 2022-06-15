<?php

namespace Draw\Component\Security\Http\Authenticator;

use Draw\Component\Messenger\Searchable\EnvelopeFinder;
use Draw\Component\Security\Http\Message\AutoConnectInterface;
use Draw\Contracts\Messenger\Exception\MessageNotFoundException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Http\Authenticator\AbstractAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;

class MessageAuthenticator extends AbstractAuthenticator
{
    private EnvelopeFinder $envelopeFinder;

    private UserProviderInterface $userProvider;

    private Security $security;

    private string $requestParameterKey;

    public function __construct(
        EnvelopeFinder $envelopeFinder,
        UserProviderInterface $userProvider,
        Security $security,
        string $requestParameterKey = 'dMUuid'
    ) {
        $this->security = $security;
        $this->userProvider = $userProvider;
        $this->envelopeFinder = $envelopeFinder;
        $this->requestParameterKey = $requestParameterKey;
    }

    public function supports(Request $request): ?bool
    {
        switch (true) {
            case null === $user = $this->getMessageUser($request->get($this->requestParameterKey)):
            case !$this->isDifferentUser($user):
                return false;
            default:
                return true;
        }
    }

    public function authenticate(Request $request): Passport
    {
        $messageId = $request->get($this->requestParameterKey);
        switch (true) {
            case null === $messageId:
            case null === $user = $this->getMessageUser($messageId):
                throw new CustomUserMessageAuthenticationException('Invalid message id.');
        }

        return new SelfValidatingPassport(
            new UserBadge($user->getUserIdentifier().'+message-'.$messageId, function () use ($user) {
                return $user;
            })
        );
    }

    private function isDifferentUser(UserInterface $connectedUser): bool
    {
        return $this->security->getUser() !== $connectedUser;
    }

    private function getMessageUser(?string $messageId): ?UserInterface
    {
        try {
            switch (true) {
                case null === $messageId:
                case null === $message = $this->envelopeFinder->findById($messageId)->getMessage():
                case !$message instanceof AutoConnectInterface:
                    return null;
            }
        } catch (MessageNotFoundException $exception) {
            return null;
        }

        return $this->userProvider->loadUserByIdentifier($message->getUserIdentifier());
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        return null;
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): ?Response
    {
        return null;
    }
}

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
    public function __construct(
        private EnvelopeFinder $envelopeFinder,
        private UserProviderInterface $userProvider,
        private Security $security,
        private string $requestParameterKey = 'dMUuid'
    ) {
    }

    public function supports(Request $request): ?bool
    {
        return match (true) {
            null === $user = $this->getMessageUser($request->get($this->requestParameterKey)), !$this->isDifferentUser($user) => false,
            default => true,
        };
    }

    public function authenticate(Request $request): Passport
    {
        $messageId = $request->get($this->requestParameterKey);

        if (!$user = $this->getMessageUser($messageId)) {
            throw new CustomUserMessageAuthenticationException('Invalid message id.');
        }

        return new SelfValidatingPassport(
            new UserBadge($user->getUserIdentifier().'+message-'.$messageId, fn () => $user)
        );
    }

    private function isDifferentUser(UserInterface $connectedUser): bool
    {
        return $this->security->getUser() !== $connectedUser;
    }

    private function getMessageUser(?string $messageId): ?UserInterface
    {
        if (null === $messageId) {
            return null;
        }

        try {
            $message = $this->envelopeFinder->findById($messageId)->getMessage();
        } catch (MessageNotFoundException) {
            return null;
        }

        if (!$message instanceof AutoConnectInterface) {
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

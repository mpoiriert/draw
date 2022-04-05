<?php

namespace Draw\Bundle\UserBundle\Security;

use Doctrine\ORM\EntityRepository;
use Draw\Bundle\MessengerBundle\Controller\MessageController;
use Draw\Bundle\UserBundle\Message\AutoConnectInterface;
use Draw\Component\Messenger\Transport\DrawTransport;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\Transport\TransportInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Http\Authenticator\AbstractAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;

class MessageAuthenticator extends AbstractAuthenticator
{
    private TransportInterface $transport;

    private EntityRepository $entityRepository;

    private Security $security;

    public function __construct(
        DrawTransport $drawTransport,
        EntityRepository $drawUserEntityRepository,
        Security $security
    ) {
        $this->security = $security;
        $this->entityRepository = $drawUserEntityRepository;
        $this->transport = $drawTransport; // todo configure which transport to use
    }

    public function supports(Request $request): ?bool
    {
        switch (true) {
            case !$request->get(MessageController::MESSAGE_ID_PARAMETER_NAME):
            case !$this->isDifferentUser($request->get(MessageController::MESSAGE_ID_PARAMETER_NAME)):
                return false;
            default:
                return true;
        }
    }

    public function authenticate(Request $request): Passport
    {
        $messageId = $request->get(MessageController::MESSAGE_ID_PARAMETER_NAME);
        switch (true) {
            case null === $messageId:
            case null === $user = $this->getMessageUser($messageId):
                throw new CustomUserMessageAuthenticationException('Invalid message id');
        }

        return new SelfValidatingPassport(
            new UserBadge($user->getUserIdentifier().'+'.$messageId, function () use ($user) {
                return $user;
            })
        );
    }

    private function isDifferentUser(string $messageId): bool
    {
        switch (true) {
            default:
            case null === $user = $this->security->getUser():
                return true;
            case $user === $this->getMessageUser($messageId):
                return false;
        }
    }

    private function getMessageUser(?string $messageId): ?UserInterface
    {
        switch (true) {
            case null === $messageId:
            case null === $envelope = $this->transport->find($messageId):
            case null === $message = $envelope->getMessage():
            case !$message instanceof AutoConnectInterface:
                return null;
        }

        return $this->entityRepository->find($message->getUserId());
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

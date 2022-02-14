<?php

namespace Draw\Bundle\UserBundle\Security;

use Doctrine\ORM\EntityRepository;
use Draw\Bundle\MessengerBundle\Controller\MessageController;
use Draw\Bundle\UserBundle\Message\AutoConnectInterface;
use Draw\Component\Messenger\Transport\DrawTransport;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Guard\AbstractGuardAuthenticator;

class MessageAuthenticator extends AbstractGuardAuthenticator
{
    private $transport;

    private $entityRepository;

    private $security;

    public function __construct(
        DrawTransport $drawTransport,
        EntityRepository $userEntityRepository,
        Security $security
    ) {
        $this->security = $security;
        $this->entityRepository = $userEntityRepository;
        $this->transport = $drawTransport;
    }

    public function supports(Request $request)
    {
        switch (true) {
            case !$request->get(MessageController::MESSAGE_ID_PARAMETER_NAME):
            case !$this->isDifferentUser($request->get(MessageController::MESSAGE_ID_PARAMETER_NAME)):
                return false;
            default:
                return true;
        }
    }

    public function getCredentials(Request $request)
    {
        return ['messageId' => $request->get(MessageController::MESSAGE_ID_PARAMETER_NAME)];
    }

    public function getUser($credentials, UserProviderInterface $userProvider)
    {
        return $this->getMessageUser($credentials['messageId'] ?? null);
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
        if (null === $messageId) {
            return null;
        }

        if (null === ($envelope = $this->transport->find($messageId))) {
            return null;
        }

        $message = $envelope->getMessage();
        if (!$message instanceof AutoConnectInterface) {
            return null;
        }

        return $this->entityRepository->find($message->getUserId());
    }

    public function start(Request $request, AuthenticationException $authException = null)
    {
        return null;
    }

    public function checkCredentials($credentials, UserInterface $user)
    {
        return true;
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception)
    {
        return null;
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, $providerKey)
    {
        return null;
    }

    public function supportsRememberMe()
    {
        return false;
    }
}

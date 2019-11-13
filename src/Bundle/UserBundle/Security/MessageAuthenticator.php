<?php namespace Draw\Bundle\UserBundle\Security;

use Doctrine\ORM\EntityRepository;
use Draw\Bundle\MessengerBundle\Controller\MessageController;
use Draw\Bundle\UserBundle\Message\AutoConnectInterface;
use Draw\Component\Messenger\Transport\DrawTransport;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Guard\AbstractGuardAuthenticator;

class MessageAuthenticator extends AbstractGuardAuthenticator
{
    private $transport;

    private $entityRepository;

    public function __construct(
        DrawTransport $drawTransport,
        EntityRepository $userEntityRepository
    )
    {
        $this->entityRepository = $userEntityRepository;
        $this->transport = $drawTransport;
    }

    public function supports(Request $request)
    {
        return $request->get(MessageController::MESSAGE_ID_PARAMETER_NAME);
    }

    public function getCredentials(Request $request)
    {
        return ['messageId' => $request->get(MessageController::MESSAGE_ID_PARAMETER_NAME)];
    }

    public function getUser($credentials, UserProviderInterface $userProvider)
    {
        $messageId = $credentials['messageId'] ?? null;
        if(is_null($messageId)) {
            return null;
        }

        if(is_null($envelope = $this->transport->find($messageId))) {
            return null;
        }

        $message = $envelope->getMessage();
        if(!$message instanceof AutoConnectInterface) {
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
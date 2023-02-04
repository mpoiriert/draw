<?php

namespace Draw\Bundle\UserBundle\Event;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Contracts\EventDispatcher\Event;

class UserRequestInterceptedEvent extends Event
{
    public function __construct(
        private UserInterface $user,
        private Request $request,
        private Response $response,
        private string $reason
    ) {
    }

    public function getReason(): string
    {
        return $this->reason;
    }

    public function getRequest(): Request
    {
        return $this->request;
    }

    public function getResponse(): Response
    {
        return $this->response;
    }

    public function getUser(): UserInterface
    {
        return $this->user;
    }
}

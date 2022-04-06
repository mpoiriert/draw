<?php

namespace Draw\Bundle\UserBundle\Event;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Contracts\EventDispatcher\Event;

class UserRequestInterceptedEvent extends Event
{
    private string $reason;

    private Request $request;

    private Response $response;

    private UserInterface $user;

    public function __construct(UserInterface $user, Request $request, Response $response, string $reason)
    {
        $this->user = $user;
        $this->request = $request;
        $this->response = $response;
        $this->reason = $reason;
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

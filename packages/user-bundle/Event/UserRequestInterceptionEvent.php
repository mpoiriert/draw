<?php

namespace Draw\Bundle\UserBundle\Event;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Contracts\EventDispatcher\Event;

class UserRequestInterceptionEvent extends Event
{
    private bool $allowHandlingRequest = false;

    private ?string $reason = null;

    private ?Response $response = null;

    public function __construct(private UserInterface $user, private Request $request)
    {
    }

    public function setResponse(Response $response, string $reason): void
    {
        $this->response = $response;
        $this->reason = $reason;
        $this->stopPropagation();
    }

    public function allowHandlingRequest(): void
    {
        $this->allowHandlingRequest = true;
        $this->stopPropagation();
    }

    public function getAllowHandlingRequest(): bool
    {
        return $this->allowHandlingRequest;
    }

    public function getUser(): UserInterface
    {
        return $this->user;
    }

    public function getReason(): ?string
    {
        return $this->reason;
    }

    public function getRequest(): Request
    {
        return $this->request;
    }

    public function getResponse(): ?Response
    {
        return $this->response;
    }
}

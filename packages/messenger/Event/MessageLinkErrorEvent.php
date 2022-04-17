<?php

namespace Draw\Component\Messenger\Event;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\EventDispatcher\Event;
use Throwable;

class MessageLinkErrorEvent extends Event
{
    private Request $request;

    private string $messageId;

    private Throwable $error;

    private ?Response $response = null;

    public function __construct(Request $request, string $messageId, Throwable $exception)
    {
        $this->request = $request;
        $this->messageId = $messageId;
        $this->error = $exception;
    }

    public function getRequest(): Request
    {
        return $this->request;
    }

    public function getMessageId(): string
    {
        return $this->messageId;
    }

    public function getError(): Throwable
    {
        return $this->error;
    }

    public function getResponse(): ?Response
    {
        return $this->response;
    }

    public function setResponse(Response $response): self
    {
        $this->response = $response;

        $this->stopPropagation();

        return $this;
    }
}

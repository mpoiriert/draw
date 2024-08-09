<?php

namespace Draw\Bundle\SonataExtraBundle\ActionableAdmin\Event;

use Draw\Bundle\SonataExtraBundle\ActionableAdmin\ObjectActionExecutioner;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\EventDispatcher\Event;

class ExecutionErrorEvent extends Event
{
    private ?Response $response = null;

    private bool $stopExecution = true;

    public function __construct(
        private \Throwable $error,
        private object $object,
        private ObjectActionExecutioner $objectActionExecutioner
    ) {
    }

    public function getError(): \Throwable
    {
        return $this->error;
    }

    public function getObject(): object
    {
        return $this->object;
    }

    public function getObjectActionExecutioner(): ObjectActionExecutioner
    {
        return $this->objectActionExecutioner;
    }

    public function getStopExecution(): bool
    {
        return $this->stopExecution;
    }

    public function setStopExecution(bool $stopExecution): self
    {
        $this->stopExecution = $stopExecution;

        return $this;
    }

    public function getResponse(): ?Response
    {
        return $this->response;
    }

    public function setResponse(?Response $response): static
    {
        $this->response = $response;

        return $this;
    }
}

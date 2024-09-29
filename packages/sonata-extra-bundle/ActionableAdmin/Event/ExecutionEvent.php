<?php

namespace Draw\Bundle\SonataExtraBundle\ActionableAdmin\Event;

use Draw\Bundle\SonataExtraBundle\ActionableAdmin\ObjectActionExecutioner;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\EventDispatcher\Event;

class ExecutionEvent extends Event
{
    public function __construct(
        private object $object,
        private ObjectActionExecutioner $objectActionExecutioner,
        private ?Response $response = null,
    ) {
    }

    public function getObject(): object
    {
        return $this->object;
    }

    public function getObjectActionExecutioner(): ObjectActionExecutioner
    {
        return $this->objectActionExecutioner;
    }

    public function skip(string $reason = 'undefined'): void
    {
        $this->objectActionExecutioner->skip($reason);

        $this->stopPropagation();
    }

    public function getResponse(): ?Response
    {
        return $this->response;
    }

    public function setResponse(?Response $response): void
    {
        $this->response = $response;
    }
}

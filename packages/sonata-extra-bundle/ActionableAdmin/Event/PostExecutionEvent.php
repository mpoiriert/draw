<?php

namespace Draw\Bundle\SonataExtraBundle\ActionableAdmin\Event;

use Draw\Bundle\SonataExtraBundle\ActionableAdmin\ObjectActionExecutioner;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\EventDispatcher\Event;

class PostExecutionEvent extends Event
{
    public function __construct(
        private ObjectActionExecutioner $objectActionExecutioner,
        private ?Response $response = null
    ) {
    }

    public function getObjectActionExecutioner(): ObjectActionExecutioner
    {
        return $this->objectActionExecutioner;
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

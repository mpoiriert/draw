<?php

namespace Draw\Component\OpenApi\Event;

use Draw\Component\OpenApi\Configuration\Serialization;
use JMS\Serializer\SerializationContext;
use Symfony\Contracts\EventDispatcher\Event;

class PreSerializerResponseEvent extends Event
{
    public function __construct(
        private mixed $result,
        private ?Serialization $serialization,
        private SerializationContext $context
    ) {
    }

    public function getResult()
    {
        return $this->result;
    }

    public function getSerialization(): ?Serialization
    {
        return $this->serialization;
    }

    public function getContext(): SerializationContext
    {
        return $this->context;
    }
}

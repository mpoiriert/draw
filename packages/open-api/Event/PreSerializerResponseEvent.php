<?php

namespace Draw\Component\OpenApi\Event;

use Draw\Component\OpenApi\Configuration\Serialization;
use JMS\Serializer\SerializationContext;
use Symfony\Contracts\EventDispatcher\Event;

class PreSerializerResponseEvent extends Event
{
    /**
     * @var mixed
     */
    private $result;

    private ?Serialization $serialization;

    private SerializationContext $context;

    public function __construct($result, ?Serialization $view, SerializationContext $context)
    {
        $this->result = $result;
        $this->serialization = $view;
        $this->context = $context;
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

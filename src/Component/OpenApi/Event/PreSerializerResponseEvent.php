<?php

namespace Draw\Component\OpenApi\Event;

use Draw\Bundle\OpenApiBundle\Response\Serialization;
use JMS\Serializer\SerializationContext;
use Symfony\Contracts\EventDispatcher\Event;

class PreSerializerResponseEvent extends Event
{
    /**
     * @var mixed
     */
    private $result;

    /**
     * @var View
     */
    private $view;

    /**
     * @var SerializationContext
     */
    private $context;

    public function __construct($result, ?Serialization $view, SerializationContext $context)
    {
        $this->result = $result;
        $this->view = $view;
        $this->context = $context;
    }

    /**
     * @return mixed
     */
    public function getResult()
    {
        return $this->result;
    }

    /**
     * @return View
     */
    public function getView()
    {
        return $this->view;
    }

    /**
     * @return SerializationContext
     */
    public function getContext()
    {
        return $this->context;
    }
}

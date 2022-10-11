<?php

namespace Draw\Component\Messenger\Transport\Serialization;

use Draw\Component\Messenger\Transport\Event\PostDecodeEvent;
use Draw\Component\Messenger\Transport\Event\PostEncodeEvent;
use Draw\Component\Messenger\Transport\Event\PreEncodeEvent;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Transport\Serialization\SerializerInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class EventDispatcherSerializerDecorator implements SerializerInterface
{
    private EventDispatcherInterface $eventDispatcher;

    private SerializerInterface $serializer;

    public function __construct(SerializerInterface $serializer, EventDispatcherInterface $eventDispatcher)
    {
        $this->serializer = $serializer;
        $this->eventDispatcher = $eventDispatcher;
    }

    public function decode(array $encodedEnvelope): Envelope
    {
        $envelope = $this->serializer->decode($encodedEnvelope);

        return $this->eventDispatcher->dispatch(new PostDecodeEvent($envelope))->getEnvelope();
    }

    public function encode(Envelope $envelope): array
    {
        $envelope = $this->eventDispatcher->dispatch(new PreEncodeEvent($envelope))->getEnvelope();

        $result = $this->serializer->encode($envelope);

        $this->eventDispatcher->dispatch(new PostEncodeEvent($envelope));

        return $result;
    }
}

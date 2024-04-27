<?php

namespace Draw\Component\Messenger\SerializerEventDispatcher;

use Draw\Component\Messenger\SerializerEventDispatcher\Event\PostDecodeEvent;
use Draw\Component\Messenger\SerializerEventDispatcher\Event\PostEncodeEvent;
use Draw\Component\Messenger\SerializerEventDispatcher\Event\PreEncodeEvent;
use Symfony\Component\DependencyInjection\Attribute\Exclude;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Transport\Serialization\PhpSerializer;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

#[Exclude]
class PhpEventDispatcherSerializerDecorator extends PhpSerializer
{
    public function __construct(
        private PhpSerializer $serializer,
        private EventDispatcherInterface $eventDispatcher
    ) {
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

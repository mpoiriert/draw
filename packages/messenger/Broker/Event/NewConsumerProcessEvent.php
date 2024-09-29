<?php

namespace Draw\Component\Messenger\Broker\Event;

use Symfony\Contracts\EventDispatcher\Event;

class NewConsumerProcessEvent extends Event
{
    public function __construct(
        private string $context,
        private array $receivers = [],
        private array $options = [],
    ) {
    }

    public function getContext(): string
    {
        return $this->context;
    }

    public function getReceivers(): array
    {
        return $this->receivers;
    }

    public function setReceivers(array $receivers): self
    {
        $this->receivers = $receivers;

        return $this;
    }

    public function getOptions(): array
    {
        return $this->options;
    }

    public function setOptions(array $options): self
    {
        $this->options = $options;

        return $this;
    }
}

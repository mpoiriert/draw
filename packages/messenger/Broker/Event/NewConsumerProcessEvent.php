<?php

namespace Draw\Component\Messenger\Broker\Event;

use Symfony\Contracts\EventDispatcher\Event;

class NewConsumerProcessEvent extends Event
{
    private array $receivers;

    private array $options;

    public function __construct(array $receivers = [], array $options = [])
    {
        $this->receivers = $receivers;
        $this->options = $options;
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

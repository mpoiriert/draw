<?php

namespace Draw\Bundle\MessengerBundle\Broker\Event;

use Symfony\Contracts\EventDispatcher\Event;

class NewConsumerProcessEvent extends Event
{
    private $receivers;

    private $options;

    private $preventStart = false;

    public function __construct(array $receivers = [], array $options = [])
    {
        $this->receivers = $receivers;
        $this->options = $options;
    }

    public function getReceivers(): array
    {
        return $this->receivers;
    }

    public function setReceivers(array $receivers): void
    {
        $this->receivers = $receivers;
    }

    public function getOptions(): array
    {
        return $this->options;
    }

    public function setOptions(array $options): void
    {
        $this->options = $options;
    }

    public function preventStart(): void
    {
        $this->preventStart = true;
    }

    public function isStartPrevented(): bool
    {
        return $this->preventStart;
    }
}

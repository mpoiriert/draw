<?php

namespace Draw\Component\Messenger\Searchable\Stamp;

use Symfony\Component\Messenger\Stamp\StampInterface;

class FoundFromTransportStamp implements StampInterface
{
    public function __construct(private string $transportName)
    {
    }

    public function getTransportName(): string
    {
        return $this->transportName;
    }
}

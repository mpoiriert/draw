<?php

namespace Draw\Component\Messenger\Searchable\Stamp;

use Symfony\Component\Messenger\Stamp\StampInterface;

class FoundFromTransportStamp implements StampInterface
{
    private string $transportName;

    public function __construct(string $transportName)
    {
        $this->transportName = $transportName;
    }

    public function getTransportName(): string
    {
        return $this->transportName;
    }
}

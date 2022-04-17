<?php

namespace Draw\Component\Messenger\Stamp;

use Symfony\Component\Messenger\Stamp\StampInterface;

class FindFromTransportStamp implements StampInterface
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

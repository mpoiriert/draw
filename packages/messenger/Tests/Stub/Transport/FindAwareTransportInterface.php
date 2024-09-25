<?php

declare(strict_types=1);

namespace Draw\Component\Messenger\Tests\Stub\Transport;

use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Transport\TransportInterface;

interface FindAwareTransportInterface extends TransportInterface
{
    public function find(string $messageId): ?Envelope;
}

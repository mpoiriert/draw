<?php

declare(strict_types=1);

namespace Draw\Component\Messenger\Tests\Mock;

use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Transport\TransportInterface;

interface MockableFindAwareTransportInterface extends TransportInterface
{
    public function find(string $messageId): ?Envelope;
}

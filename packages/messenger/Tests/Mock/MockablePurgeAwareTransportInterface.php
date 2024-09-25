<?php

declare(strict_types=1);

namespace Draw\Component\Messenger\Tests\Mock;

use Symfony\Component\Messenger\Transport\TransportInterface;

interface MockablePurgeAwareTransportInterface extends TransportInterface
{
    public function purgeObsoleteMessages(\DateTimeInterface $since): int;
}

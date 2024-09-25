<?php

declare(strict_types=1);

namespace Draw\Component\Messenger\Tests\Stub\Transport;

use Symfony\Component\Messenger\Transport\TransportInterface;

interface PurgeAwareTransportInterface extends TransportInterface
{
    public function purgeObsoleteMessages(\DateTimeInterface $since): int;
}

<?php

namespace Draw\Contracts\Messenger;

use Psr\Container\NotFoundExceptionInterface;
use Symfony\Component\Messenger\Transport\TransportInterface;

interface TransportRepositoryInterface
{
    public function has(string $transportName): bool;

    /**
     * @throws NotFoundExceptionInterface
     */
    public function get(string $transportName): TransportInterface;

    /**
     * @return array<string>
     */
    public function getTransportNames(): array;

    /**
     * Return an assoc array with transport name and TransportInterface.
     *
     * @return iterable<string,TransportInterface>
     */
    public function findAll(): iterable;
}

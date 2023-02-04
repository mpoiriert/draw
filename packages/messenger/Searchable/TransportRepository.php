<?php

namespace Draw\Component\Messenger\Searchable;

use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Component\Messenger\Transport\TransportInterface;

class TransportRepository
{
    public function __construct(private ContainerInterface $transportLocator, private array $transportNames = [])
    {
    }

    public function has(string $transportName): bool
    {
        return $this->transportLocator->has($transportName);
    }

    /**
     * @throws NotFoundExceptionInterface
     */
    public function get(string $transportName): TransportInterface
    {
        return $this->transportLocator->get($transportName);
    }

    /**
     * @return array|string[]
     */
    public function getTransportNames(): array
    {
        return $this->transportNames;
    }

    /**
     * @return iterable<string,TransportInterface>
     */
    public function findAll(): iterable
    {
        foreach ($this->transportNames as $transportName) {
            yield $transportName => $this->transportLocator->get($transportName);
        }
    }
}

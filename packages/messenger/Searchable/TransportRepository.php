<?php

namespace Draw\Component\Messenger\Searchable;

use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Component\Messenger\Transport\TransportInterface;

class TransportRepository
{
    private ContainerInterface $transportLocator;
    private array $transportNames;

    public function __construct(ContainerInterface $transportLocator, array $transportNames = [])
    {
        $this->transportLocator = $transportLocator;
        $this->transportNames = $transportNames;
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
     * @return array|TransportInterface[]
     */
    public function findAll(): iterable
    {
        foreach ($this->transportNames as $transportName) {
            yield $transportName => $this->transportLocator->get($transportName);
        }
    }
}
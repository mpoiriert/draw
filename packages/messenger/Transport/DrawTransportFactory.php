<?php

namespace Draw\Component\Messenger\Transport;

use Doctrine\Persistence\ConnectionRegistry;
use Symfony\Bridge\Doctrine\RegistryInterface;
use Symfony\Component\Messenger\Exception\InvalidArgumentException;
use Symfony\Component\Messenger\Exception\TransportException;
use Symfony\Component\Messenger\Transport\Doctrine\Connection;
use Symfony\Component\Messenger\Transport\Doctrine\DoctrineTransportFactory;
use Symfony\Component\Messenger\Transport\Serialization\SerializerInterface;
use Symfony\Component\Messenger\Transport\TransportInterface;

class DrawTransportFactory extends DoctrineTransportFactory
{
    private const DEFAULT_OPTIONS = [
        'table_name' => 'draw_messenger__message',
        'tag_table_name' => '', // Will append _tag to the table_name
        'queue_name' => 'default',
        'redeliver_timeout' => 3600,
        'auto_setup' => false,
    ];

    /**
     * @var RegistryInterface|ConnectionRegistry
     */
    private $registry;

    public function __construct($registry)
    {
        parent::__construct($registry);
        $this->registry = $registry;
    }

    public function createTransport(string $dsn, array $options, SerializerInterface $serializer): TransportInterface
    {
        unset($options['transport_name']);
        $configuration = static::buildConfiguration($dsn, $options);

        try {
            /** @var \Doctrine\DBAL\Connection $driverConnection */
            $driverConnection = $this->registry->getConnection($configuration['connection']);
        } catch (\InvalidArgumentException $e) {
            throw new TransportException(sprintf('Could not find Doctrine connection from Messenger DSN "%s".', $dsn), 0, $e);
        }

        $connection = new Connection($configuration, $driverConnection);

        return new DrawTransport($driverConnection, $connection, $serializer);
    }

    public static function buildConfiguration(string $dsn, array $options = []): array
    {
        if (false === $components = parse_url($dsn)) {
            throw new InvalidArgumentException(sprintf('The given Doctrine Messenger DSN "%s" is invalid.', $dsn));
        }

        $query = [];
        if (isset($components['query'])) {
            parse_str($components['query'], $query);
        }

        $configuration = ['connection' => $components['host']];
        $configuration += $options + $query + self::DEFAULT_OPTIONS;

        if (!$configuration['tag_table_name']) {
            $configuration['tag_table_name'] = $configuration['table_name'].'_tag';
        }

        $configuration['auto_setup'] = filter_var($configuration['auto_setup'], FILTER_VALIDATE_BOOLEAN);

        // check for extra keys in options
        $optionsExtraKeys = array_diff(array_keys($options), array_keys(self::DEFAULT_OPTIONS));
        if (0 < \count($optionsExtraKeys)) {
            throw new InvalidArgumentException(sprintf('Unknown option found : [%s]. Allowed options are [%s]', implode(', ', $optionsExtraKeys), implode(', ', self::DEFAULT_OPTIONS)));
        }

        // check for extra keys in options
        $queryExtraKeys = array_diff(array_keys($query), array_keys(self::DEFAULT_OPTIONS));
        if (0 < \count($queryExtraKeys)) {
            throw new InvalidArgumentException(sprintf('Unknown option found in DSN: [%s]. Allowed options are [%s]', implode(', ', $queryExtraKeys), implode(', ', self::DEFAULT_OPTIONS)));
        }

        return $configuration;
    }

    public function supports(string $dsn, array $options): bool
    {
        return 0 === strpos($dsn, 'draw://');
    }
}

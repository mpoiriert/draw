<?php

namespace Draw\Component\Messenger\Transport;

use Doctrine\Persistence\ConnectionRegistry;
use Symfony\Component\Messenger\Bridge\Doctrine\Transport\Connection;
use Symfony\Component\Messenger\Bridge\Doctrine\Transport\DoctrineTransportFactory;
use Symfony\Component\Messenger\Exception\InvalidArgumentException;
use Symfony\Component\Messenger\Exception\TransportException;
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

    private ConnectionRegistry $registry;

    public function __construct(ConnectionRegistry $registry)
    {
        parent::__construct($registry);
        $this->registry = $registry;
    }

    /**
     * @return TransportInterface&DrawTransport
     */
    public function createTransport(string $dsn, array $options, SerializerInterface $serializer): TransportInterface
    {
        unset($options['transport_name']);
        $configuration = static::buildConfiguration($dsn, $options);

        try {
            /** @var \Doctrine\DBAL\Connection $driverConnection */
            $driverConnection = $this->registry->getConnection($configuration['connection']);
        } catch (\InvalidArgumentException $error) {
            throw new TransportException(sprintf('Could not find Doctrine connection from Messenger DSN "%s".', $dsn), 0, $error);
        }

        $connection = new Connection($configuration, $driverConnection);

        return new DrawTransport($driverConnection, $connection, $serializer);
    }

    public static function buildConfiguration(string $dsn, array $options = []): array
    {
        if ((false === $components = parse_url($dsn)) || !isset($components['host'])) {
            throw new InvalidArgumentException(sprintf('The given Draw Messenger DSN "%s" is invalid.', $dsn));
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

        $configuration['auto_setup'] = filter_var($configuration['auto_setup'] ?? false, \FILTER_VALIDATE_BOOLEAN);
        $configuration['redeliver_timeout'] = filter_var($configuration['redeliver_timeout'] ?? 3600, \FILTER_VALIDATE_INT);

        // check for extra keys in options
        $optionsExtraKeys = array_diff(array_keys($options), array_keys(self::DEFAULT_OPTIONS));
        if (0 < \count($optionsExtraKeys)) {
            throw new InvalidArgumentException(sprintf('Unknown option found : [%s]. Allowed options are [%s]', implode(', ', $optionsExtraKeys), implode(', ', array_keys(self::DEFAULT_OPTIONS))));
        }

        // check for extra keys in options
        $queryExtraKeys = array_diff(array_keys($query), array_keys(self::DEFAULT_OPTIONS));
        if (0 < \count($queryExtraKeys)) {
            throw new InvalidArgumentException(sprintf('Unknown option found in DSN: [%s]. Allowed options are [%s]', implode(', ', $queryExtraKeys), implode(', ', array_keys(self::DEFAULT_OPTIONS))));
        }

        return $configuration;
    }

    public function supports(string $dsn, array $options): bool
    {
        return 0 === strpos($dsn, 'draw://');
    }
}

<?php namespace Draw\Component\Messenger\Transport;

use Symfony\Bridge\Doctrine\RegistryInterface;
use Symfony\Component\Messenger\Exception\InvalidArgumentException;
use Symfony\Component\Messenger\Exception\TransportException;
use Symfony\Component\Messenger\Transport\Doctrine\Connection;
use Symfony\Component\Messenger\Transport\Doctrine\DoctrineTransportFactory;
use Symfony\Component\Messenger\Transport\Serialization\SerializerInterface;
use Symfony\Component\Messenger\Transport\TransportInterface;

class DrawTransportFactory extends DoctrineTransportFactory
{
    private $registry;

    public function __construct(RegistryInterface $registry)
    {
        $this->registry = $registry;
        parent::__construct($registry);
    }

    public function createTransport(string $dsn, array $options, SerializerInterface $serializer): TransportInterface
    {
        if (false === $components = parse_url($dsn)) {
            throw new InvalidArgumentException(sprintf('The given Draw Messenger DSN "%s" is invalid.', $dsn));
        }

        if(isset($components['query'])) {
            $dsn .= '&';
        } else {
            $dsn .= '?';
        }

        $dsn .= 'auto_setup=false';

        if(!isset($components['query']) || strpos($components['query'], 'table_name') === false) {
            $dsn .= '&table_name=draw_messenger__message';
        }

        unset($options['transport_name']);
        $configuration = Connection::buildConfiguration($dsn, $options);

        try {
            /** @var \Doctrine\DBAL\Connection $driverConnection */
            $driverConnection = $this->registry->getConnection($configuration['connection']);
        } catch (\InvalidArgumentException $e) {
            throw new TransportException(sprintf('Could not find Doctrine connection from Messenger DSN "%s".', $dsn), 0, $e);
        }

        $connection = new Connection($configuration, $driverConnection);
        return new DrawTransport($driverConnection, $connection, $serializer);
    }

    public function supports(string $dsn, array $options): bool
    {
        return 0 === strpos($dsn, 'draw://');
    }
}
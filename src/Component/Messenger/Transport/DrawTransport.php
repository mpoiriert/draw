<?php namespace Draw\Component\Messenger\Transport;

use Doctrine\DBAL\Connection as DBALConnection;
use Doctrine\DBAL\DBALException;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Schema\Synchronizer\SingleDatabaseSynchronizer;
use Doctrine\DBAL\Types\Type;
use Draw\Component\Messenger\Stamp\ExpirationStamp;
use Draw\Component\Messenger\Stamp\ManualTriggerStamp;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Exception\TransportException;
use Symfony\Component\Messenger\Stamp\DelayStamp;
use Symfony\Component\Messenger\Stamp\TransportMessageIdStamp;
use Symfony\Component\Messenger\Transport\Doctrine\Connection;
use Symfony\Component\Messenger\Transport\Doctrine\DoctrineTransport;
use Symfony\Component\Messenger\Transport\Serialization\SerializerInterface;

class DrawTransport extends DoctrineTransport
{
    private $driverConnection;

    private $connection;

    private $schemaSynchronizer;

    private $serializer;

    public function __construct(DBALConnection $driverConnection, Connection $connection, SerializerInterface $serializer)
    {
        $this->driverConnection = $driverConnection;
        $this->connection = $connection;
        $this->serializer = $serializer;
        $this->schemaSynchronizer = new SingleDatabaseSynchronizer($this->driverConnection);
        parent::__construct($connection, $serializer);
    }

    /**
     * {@inheritdoc}
     */
    public function send(Envelope $envelope): Envelope
    {
        $encodedMessage = $this->serializer->encode($envelope);

        $delayStamp = $envelope->last(DelayStamp::class);
        $delay = null !== $delayStamp ? $delayStamp->getDelay() : null;
        $delay = $delay ?? $envelope->last(ManualTriggerStamp::class) ? null : 0;
        $expirationStamp = $envelope->last(ExpirationStamp::class);
        $expiresAt = $expirationStamp ? $expirationStamp->getDateTime() : null;
        try {
            $id = $this->insert(
                $encodedMessage['body'],
                $encodedMessage['headers'] ?? [],
                $delay,
                $expiresAt
            );
        } catch (DBALException $exception) {
            throw new TransportException($exception->getMessage(), 0, $exception);
        }

        return $envelope->with(new TransportMessageIdStamp($id));
    }

    private function insert(string $body, array $headers, int $delay = null, \DateTimeInterface $expiresAt = null)
    {
        $id = Uuid::uuid4()->toString();
        $now = new \DateTime();
        $availableAt = null;
        if(!is_null($delay)) {
            $availableAt = (clone $now)->modify(sprintf('+%d seconds', $delay / 1000));
        }

        $queryBuilder = $this->driverConnection->createQueryBuilder()
            ->insert($this->connection->getConfiguration()['table_name'])
            ->values([
                'id' => '?',
                'body' => '?',
                'headers' => '?',
                'queue_name' => '?',
                'created_at' => '?',
                'available_at' => '?',
                'expires_at' => '?',
            ]);

        $this->executeQuery($queryBuilder->getSQL(), [
            $id,
            $body,
            json_encode($headers),
            $this->connection->getConfiguration()['queue_name'],
            Connection::formatDateTime($now),
            $availableAt ? Connection::formatDateTime($availableAt) : null,
            $expiresAt ? Connection::formatDateTime($expiresAt) : null
        ]);

        return $id;
    }

    private function executeQuery(string $sql, array $parameters = [])
    {
        $stmt = $this->driverConnection->prepare($sql);
        $stmt->execute($parameters);
        return $stmt;
    }

    public function setup(): void
    {
        $configuration = $this->driverConnection->getConfiguration();
        // Since Doctrine 2.9 the getFilterSchemaAssetsExpression is deprecated
        $hasFilterCallback = method_exists($configuration, 'getSchemaAssetsFilter');

        if ($hasFilterCallback) {
            $assetFilter = $this->driverConnection->getConfiguration()->getSchemaAssetsFilter();
            $this->driverConnection->getConfiguration()->setSchemaAssetsFilter(null);
        } else {
            $assetFilter = $this->driverConnection->getConfiguration()->getFilterSchemaAssetsExpression();
            $this->driverConnection->getConfiguration()->setFilterSchemaAssetsExpression(null);
        }

        $this->schemaSynchronizer->updateSchema($this->getSchema(), true);

        if ($hasFilterCallback) {
            $this->driverConnection->getConfiguration()->setSchemaAssetsFilter($assetFilter);
        } else {
            $this->driverConnection->getConfiguration()->setFilterSchemaAssetsExpression($assetFilter);
        }
    }

    private function getSchema(): Schema
    {
        $schema = new Schema([], [], $this->driverConnection->getSchemaManager()->createSchemaConfig());
        $table = $schema->createTable($this->connection->getConfiguration()['table_name']);
        $table->addColumn('id', Type::GUID)
            ->setNotnull(true);
        $table->addColumn('body', Type::TEXT)
            ->setNotnull(true);
        $table->addColumn('headers', Type::TEXT)
            ->setNotnull(true);
        $table->addColumn('queue_name', Type::STRING)
            ->setNotnull(true);
        $table->addColumn('created_at', Type::DATETIME)
            ->setNotnull(true);
        $table->addColumn('available_at', Type::DATETIME)
            ->setNotnull(false);
        $table->addColumn('delivered_at', Type::DATETIME)
            ->setNotnull(false);
        $table->addColumn('expires_at', Type::DATETIME)
            ->setNotnull(false);
        $table->setPrimaryKey(['id']);
        $table->addIndex(['queue_name']);
        $table->addIndex(['available_at']);
        $table->addIndex(['delivered_at']);

        return $schema;
    }
}
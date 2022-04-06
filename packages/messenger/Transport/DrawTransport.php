<?php

namespace Draw\Component\Messenger\Transport;

use DateTime;
use DateTimeInterface;
use Doctrine\DBAL\Connection as DBALConnection;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Schema\Synchronizer\SingleDatabaseSynchronizer;
use Doctrine\DBAL\Types\Types;
use Draw\Component\Messenger\Stamp\ExpirationStamp;
use Draw\Component\Messenger\Stamp\ManualTriggerStamp;
use Draw\Component\Messenger\Stamp\SearchableTagStamp;
use Exception;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Exception\TransportException;
use Symfony\Component\Messenger\Stamp\DelayStamp;
use Symfony\Component\Messenger\Stamp\RedeliveryStamp;
use Symfony\Component\Messenger\Stamp\TransportMessageIdStamp;
use Symfony\Component\Messenger\Transport\Doctrine\Connection;
use Symfony\Component\Messenger\Transport\Doctrine\DoctrineReceivedStamp;
use Symfony\Component\Messenger\Transport\Doctrine\DoctrineTransport;
use Symfony\Component\Messenger\Transport\Serialization\SerializerInterface;

class DrawTransport extends DoctrineTransport implements ObsoleteMessageAwareInterface, SearchableInterface
{
    private $driverConnection;

    private $connection;

    private $schemaSynchronizer;

    private $serializer;

    public function __construct(
        DBALConnection $driverConnection,
        Connection $connection,
        SerializerInterface $serializer
    ) {
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
        $envelope = $envelope
            ->withoutAll(TransportMessageIdStamp::class)
            ->withoutAll(DoctrineReceivedStamp::class);

        $encodedMessage = $this->serializer->encode($envelope);

        $delayStamp = $envelope->last(DelayStamp::class);
        $delay = null !== $delayStamp ? $delayStamp->getDelay() : null;
        $delay = $delay ?? ($envelope->last(ManualTriggerStamp::class) ? null : 0);
        $expirationStamp = $envelope->last(ExpirationStamp::class);
        $expiresAt = $expirationStamp ? $expirationStamp->getDateTime() : null;

        $this->cleanQueue($envelope);

        try {
            $id = $this->insert(
                $encodedMessage['body'],
                $encodedMessage['headers'] ?? [],
                $delay,
                $expiresAt,
                $this->getTags($envelope)
            );
        } catch (Exception $exception) {
            throw new TransportException($exception->getMessage(), 0, $exception);
        }

        return $envelope->with(new TransportMessageIdStamp($id));
    }

    private function cleanQueue(Envelope $envelope): void
    {
        if ($envelope->last(RedeliveryStamp::class)) {
            return;
        }

        foreach ($envelope->all(SearchableTagStamp::class) as $stamp) {
            if (!$stamp->getEnforceUniqueness()) {
                continue;
            }

            if (!($tags = $stamp->getTags())) {
                continue;
            }

            $ids = $this->findEnvelopeIds($tags);

            if (!$ids) {
                continue;
            }

            $sql = $this->driverConnection->createQueryBuilder()
                ->delete($this->connection->getConfiguration()['table_name'])
                ->andWhere('id IN ("'.implode('","', $ids).'")')
                ->getSQL();

            $this->driverConnection->executeStatement($sql);
        }
    }

    private function getTags(Envelope $envelope): array
    {
        $tags = [];
        /** @var SearchableTagStamp $stamp */
        foreach ($envelope->all(SearchableTagStamp::class) as $stamp) {
            $tags = array_merge($stamp->getTags(), $tags);
        }

        return array_values(array_unique($tags));
    }

    private function insert(
        string $body,
        array $headers,
        int $delay = null,
        DateTimeInterface $expiresAt = null,
        array $tags = []
    ): string {
        $id = Uuid::uuid6()->toString();
        $now = new DateTime();
        $availableAt = null;
        if (null !== $delay) {
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
        $this->driverConnection
            ->prepare($queryBuilder->getSQL())
            ->executeStatement([
                $id,
                $body,
                json_encode($headers),
                $this->connection->getConfiguration()['queue_name'],
                self::formatDateTime($now),
                $availableAt ? self::formatDateTime($availableAt) : null,
                $expiresAt ? self::formatDateTime($expiresAt) : null,
            ]);

        if ($tags) {
            $queryBuilder = $this->driverConnection->createQueryBuilder()
                ->insert($this->connection->getConfiguration()['tag_table_name'])
                ->values(['message_id' => '?', 'name' => '?']);
            $statement = $this->driverConnection
                ->prepare($queryBuilder->getSQL());
            foreach ($tags as $tag) {
                $statement->executeStatement([$id, $tag]);
            }
        }

        return $id;
    }

    private static function formatDateTime(DateTimeInterface $dateTime): string
    {
        return $dateTime->format('Y-m-d\TH:i:s');
    }

    public function setup(): void
    {
        $configuration = $this->driverConnection->getConfiguration();

        $assetFilter = $configuration->getSchemaAssetsFilter();
        $configuration->setSchemaAssetsFilter();

        $this->schemaSynchronizer->updateSchema($this->getSchema(), true);

        $configuration->setSchemaAssetsFilter($assetFilter);
    }

    public function findByTag(string $tag): array
    {
        return $this->findByTags([$tag]);
    }

    public function findByTags(array $tags): array
    {
        $ids = $this->findEnvelopeIds($tags);

        $envelopes = [];
        foreach ($ids as $id) {
            $envelopes[] = $this->find($id);
        }

        // The find above can return null
        return array_values(array_filter($envelopes));
    }

    /**
     * @param string[] $tags
     *
     * @return string[]
     */
    private function findEnvelopeIds(array $tags): array
    {
        if (!$tags) {
            return [];
        }

        $queryBuilder = $this->driverConnection->createQueryBuilder();
        $queryBuilder
            ->select('message.id')
            ->from($this->connection->getConfiguration()['table_name'], 'message')
            ->andWhere('message.queue_name = ?')
            // From DoctrineTransport patch for deadlock
            ->andWhere(
                $queryBuilder->expr()->or(
                    $queryBuilder->expr()->neq('message.delivered_at', '?'),
                    $queryBuilder->expr()->isNull('message.delivered_at')
                )
            );

        foreach ($tags as $index => $tag) {
            $tagAlias = 'tag_'.$index;
            $queryBuilder = $queryBuilder
                ->innerJoin(
                    'message',
                    $this->connection->getConfiguration()['tag_table_name'],
                    $tagAlias,
                    'message.id = '.$tagAlias.'.message_id'
                )
                ->andWhere($tagAlias.'.name = ?');
        }

        $rows = $this->driverConnection->executeQuery(
            $queryBuilder->getSQL(),
            array_merge(
                [$this->connection->getConfiguration()['queue_name']],
                ['9999-12-31'],
                $tags
            )
        )->fetchAllAssociative();

        $ids = [];
        foreach ($rows as $row) {
            $ids[] = $row['id'];
        }

        return array_values(array_unique($ids));
    }

    public function purgeObsoleteMessages(DateTimeInterface $since): int
    {
        $tableName = $this->connection->getConfiguration()['table_name'];
        $batchSize = 1000;
        $seconds = 10;

        $total = 0;
        do {
            $total += $affectedRows = $this->driverConnection->executeStatement(
                'DELETE FROM '.$tableName.' WHERE expires_at < ? LIMIT ?',
                [$since, $batchSize],
                [Types::DATETIME_IMMUTABLE, Types::INTEGER]
            );

            if ($affectedRows < $batchSize) {
                break;
            }

            usleep($seconds * 1000000);
        } while (true);

        return $total;
    }

    private function getSchema(): Schema
    {
        $messagesTableName = $this->connection->getConfiguration()['table_name'];
        $tagsTableName = $this->connection->getConfiguration()['tag_table_name'];
        $schema = new Schema([], [], $this->driverConnection->getSchemaManager()->createSchemaConfig());

        $messageTable = $schema->createTable($messagesTableName);
        $messageTable
            ->addColumn('id', Types::GUID)
            ->setNotnull(true);
        $messageTable
            ->addColumn('body', Types::TEXT)
            ->setNotnull(true);
        $messageTable
            ->addColumn('headers', Types::TEXT)
            ->setNotnull(true);
        $messageTable
            ->addColumn('queue_name', Types::STRING)
            ->setNotnull(true);
        $messageTable
            ->addColumn('created_at', Types::DATETIME_IMMUTABLE)
            ->setNotnull(true);
        $messageTable
            ->addColumn('available_at', Types::DATETIME_IMMUTABLE)
            ->setNotnull(false);
        $messageTable
            ->addColumn('delivered_at', Types::DATETIME_IMMUTABLE)
            ->setNotnull(false);
        $messageTable
            ->addColumn('expires_at', Types::DATETIME_IMMUTABLE)
            ->setNotnull(false);
        $messageTable->setPrimaryKey(['id']);
        $messageTable->addIndex(['queue_name', 'available_at']);
        $messageTable->addIndex(['available_at']);
        $messageTable->addIndex(['delivered_at']);
        $messageTable->addIndex(['expires_at']);

        $tagTable = $schema->createTable($tagsTableName);

        $tagTable
            ->addColumn('message_id', Types::GUID)
            ->setNotnull(true);
        $tagTable
            ->addColumn('name', Types::STRING)
            ->setNotnull(true);
        $tagTable->setPrimaryKey(['message_id', 'name']);
        $tagTable->addIndex(['name']);
        $tagTable->addForeignKeyConstraint($messagesTableName, ['message_id'], ['id'], ['onDelete' => 'CASCADE']);

        return $schema;
    }
}

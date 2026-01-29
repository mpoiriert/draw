<?php

namespace Draw\Component\Messenger\Transport;

use Doctrine\DBAL\Connection as DBALConnection;
use Doctrine\DBAL\Platforms\MySQLPlatform;
use Doctrine\DBAL\Schema\PrimaryKeyConstraint;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Types\Types;
use Draw\Component\Core\DateTimeUtils;
use Draw\Component\Messenger\Expirable\PurgeableTransportInterface;
use Draw\Component\Messenger\Expirable\Stamp\ExpirationStamp;
use Draw\Component\Messenger\ManualTrigger\Stamp\ManualTriggerStamp;
use Draw\Component\Messenger\Searchable\SearchableTransportInterface;
use Draw\Component\Messenger\Searchable\Stamp\SearchableTagStamp;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Messenger\Bridge\Doctrine\Transport\Connection;
use Symfony\Component\Messenger\Bridge\Doctrine\Transport\DoctrineReceivedStamp;
use Symfony\Component\Messenger\Bridge\Doctrine\Transport\DoctrineTransport;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Exception\TransportException;
use Symfony\Component\Messenger\Stamp\DelayStamp;
use Symfony\Component\Messenger\Stamp\RedeliveryStamp;
use Symfony\Component\Messenger\Stamp\TransportMessageIdStamp;
use Symfony\Component\Messenger\Transport\Serialization\SerializerInterface;

class DrawTransport extends DoctrineTransport implements PurgeableTransportInterface, SearchableTransportInterface
{
    public function __construct(
        private DBALConnection $driverConnection,
        private Connection $connection,
        private SerializerInterface $serializer,
    ) {
        parent::__construct($connection, $serializer);
    }

    public function send(Envelope $envelope): Envelope
    {
        $envelope = $envelope
            ->withoutAll(TransportMessageIdStamp::class)
            ->withoutAll(DoctrineReceivedStamp::class)
        ;

        $encodedMessage = $this->serializer->encode($envelope);

        $delayStamp = $envelope->last(DelayStamp::class);
        $delay = $delayStamp?->getDelay();
        $delay ??= $envelope->last(ManualTriggerStamp::class) ? null : 0;
        $expirationStamp = $envelope->last(ExpirationStamp::class);
        $expiresAt = $expirationStamp?->getDateTime();

        $this->cleanQueue($envelope);

        try {
            $id = $this->insert(
                $encodedMessage['body'],
                $encodedMessage['headers'] ?? [],
                $delay,
                $expiresAt,
                $this->getTags($envelope),
                \get_class($envelope->getMessage())
            );
        } catch (\Exception $exception) {
            throw new TransportException($exception->getMessage(), 0, $exception);
        }

        return $envelope->with(new TransportMessageIdStamp($id));
    }

    /**
     * Override because doctrine transport do not filter by delivered_at date.
     */
    public function find($id): ?Envelope
    {
        if ($this->driverConnection->getDatabasePlatform() instanceof MySQLPlatform) {
            $tableName = $this->connection->getConfiguration()['table_name'];
            $this->driverConnection->executeStatement(
                \sprintf(
                    'DELETE FROM %s WHERE id = ? AND delivered_at >= ?',
                    $tableName
                ),
                [$id, '9999-12-31']
            );
        }

        return parent::find($id);
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
                ->getSQL()
            ;

            $this->driverConnection->executeStatement($sql);
        }
    }

    private function getTags(Envelope $envelope): array
    {
        $tags = [];
        /** @var SearchableTagStamp $stamp */
        foreach ($envelope->all(SearchableTagStamp::class) as $stamp) {
            array_push($tags, ...$stamp->getTags());
        }

        return array_values(array_unique($tags));
    }

    private function insert(
        string $body,
        array $headers,
        ?int $delay = null,
        ?\DateTimeInterface $expiresAt = null,
        array $tags = [],
        ?string $messageClass = null,
    ): string {
        $id = Uuid::uuid6()->toString();
        $now = new \DateTimeImmutable();
        $availableAt = null;
        if (null !== $delay) {
            $availableAt = $now->modify(\sprintf('%d seconds', $delay / 1000));
        }

        $queryBuilder = $this->driverConnection->createQueryBuilder()
            ->insert($this->connection->getConfiguration()['table_name'])
            ->values([
                'id' => '?',
                'message_class' => '?',
                'body' => '?',
                'headers' => '?',
                'queue_name' => '?',
                'created_at' => '?',
                'available_at' => '?',
                'expires_at' => '?',
            ])
        ;
        $this->driverConnection->executeStatement(
            $queryBuilder->getSQL(),
            [
                $id,
                $messageClass ? substr($messageClass, -255) : null,
                $body,
                json_encode($headers, \JSON_THROW_ON_ERROR),
                $this->connection->getConfiguration()['queue_name'],
                self::formatDateTime($now),
                self::formatDateTime($availableAt),
                self::formatDateTime($expiresAt),
            ]);

        if ($tags) {
            $queryBuilder = $this->driverConnection->createQueryBuilder()
                ->insert($this->connection->getConfiguration()['tag_table_name'])
                ->values(['message_id' => '?', 'name' => '?'])
            ;
            foreach ($tags as $tag) {
                $this->driverConnection->executeStatement(
                    $queryBuilder->getSQL(),
                    [$id, $tag]
                );
            }
        }

        return $id;
    }

    private static function formatDateTime(?\DateTimeInterface $dateTime): ?string
    {
        return $dateTime?->format('Y-m-d\TH:i:s');
    }

    public function setup(): void
    {
        $configuration = $this->driverConnection->getConfiguration();

        $assetFilter = $configuration->getSchemaAssetsFilter();
        $configuration->setSchemaAssetsFilter(static fn (): bool => true);

        $schemaManager = $this->driverConnection->createSchemaManager();
        $comparator = $schemaManager->createComparator();
        $schemaDiff = $comparator->compareSchemas(
            $schemaManager->introspectSchema(),
            $this->getSchema()
        );

        $platform = $this->driverConnection->getDatabasePlatform();
        $sqls = $platform->getAlterSchemaSQL($schemaDiff);

        foreach ($sqls as $sql) {
            $this->driverConnection->executeStatement($sql);
        }

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
            )
        ;

        foreach ($tags as $index => $tag) {
            $tagAlias = 'tag_'.$index;
            $queryBuilder = $queryBuilder
                ->innerJoin(
                    'message',
                    $this->connection->getConfiguration()['tag_table_name'],
                    $tagAlias,
                    'message.id = '.$tagAlias.'.message_id'
                )
                ->andWhere($tagAlias.'.name = ?')
            ;
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

    public function purgeObsoleteMessages(\DateTimeInterface $since): int
    {
        $tableName = $this->connection->getConfiguration()['table_name'];

        return (int) $this->driverConnection->executeStatement(
            'DELETE FROM '.$tableName.' WHERE expires_at < ?',
            [DateTimeUtils::toDateTimeImmutable($since)],
            [Types::DATETIME_IMMUTABLE]
        );
    }

    private function getSchema(): Schema
    {
        $messagesTableName = $this->connection->getConfiguration()['table_name'];
        $tagsTableName = $this->connection->getConfiguration()['tag_table_name'];
        $schema = new Schema([], [], $this->driverConnection->createSchemaManager()->createSchemaConfig());

        $messageTable = $schema->createTable($messagesTableName);
        $messageTable
            ->addColumn('id', Types::GUID)
            ->setNotnull(true)
        ;
        $messageTable
            ->addColumn('message_class', Types::STRING, ['length' => 255])
            ->setNotnull(false)
        ;
        $messageTable
            ->addColumn('body', Types::TEXT)
            ->setNotnull(true)
        ;
        $messageTable
            ->addColumn('headers', Types::TEXT)
            ->setNotnull(true)
        ;
        $messageTable
            ->addColumn('queue_name', Types::STRING, ['length' => 255])
            ->setNotnull(true)
        ;
        $messageTable
            ->addColumn('created_at', Types::DATETIME_IMMUTABLE)
            ->setNotnull(true)
        ;
        $messageTable
            ->addColumn('available_at', Types::DATETIME_IMMUTABLE)
            ->setNotnull(false)
        ;
        $messageTable
            ->addColumn('delivered_at', Types::DATETIME_IMMUTABLE)
            ->setNotnull(false)
        ;
        $messageTable
            ->addColumn('expires_at', Types::DATETIME_IMMUTABLE)
            ->setNotnull(false)
        ;
        $messageTable->addPrimaryKeyConstraint(
            PrimaryKeyConstraint::editor()
                ->setUnquotedColumnNames('id')
                ->create()
        );
        $messageTable->addIndex(['message_class', 'available_at']);
        $messageTable->addIndex(['queue_name', 'available_at']);
        $messageTable->addIndex(['available_at']);
        $messageTable->addIndex(['delivered_at']);
        $messageTable->addIndex(['expires_at']);

        $tagTable = $schema->createTable($tagsTableName);

        $tagTable
            ->addColumn('message_id', Types::GUID)
            ->setNotnull(true)
        ;
        $tagTable
            ->addColumn('name', Types::STRING, ['length' => 255])
            ->setNotnull(true)
        ;
        $tagTable->addPrimaryKeyConstraint(
            PrimaryKeyConstraint::editor()
                ->setUnquotedColumnNames('message_id', 'name')
                ->create()
        );
        $tagTable->addIndex(['name']);
        $tagTable->addForeignKeyConstraint($messagesTableName, ['message_id'], ['id'], ['onDelete' => 'CASCADE']);

        return $schema;
    }
}

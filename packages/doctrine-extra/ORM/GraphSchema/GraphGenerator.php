<?php

namespace Draw\DoctrineExtra\ORM\GraphSchema;

use Doctrine\DBAL\Schema\Visitor\Graphviz;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Tools\SchemaTool;
use Psr\EventDispatcher\EventDispatcherInterface;

class GraphGenerator
{
    public function __construct(
        private EventDispatcherInterface $eventDispatcher,
    ) {
    }

    public function generate(Context $context): string
    {
        $this->eventDispatcher->dispatch(new Event\PrepareContextEvent($context));

        $entityManager = $context->getEntityManager();

        /** @var array<int, ClassMetadata<object>> $metadata */
        $metadata = $entityManager->getMetadataFactory()->getAllMetadata();

        usort($metadata, static fn (ClassMetadata $a, ClassMetadata $b): int => $a->getTableName() <=> $b->getTableName());

        $tool = new SchemaTool($entityManager);
        $schema = $tool->getSchemaFromMetadata($metadata);

        $visitor = new Graphviz();

        $visitor->acceptSchema($schema);

        $ignoreTables = $this->buildIgnoreTables($context);

        foreach ($schema->getTables() as $table) {
            if (\in_array($table->getName(), $ignoreTables, true)) {
                continue;
            }

            $visitor->acceptTable($table);
            foreach ($table->getColumns() as $column) {
                $visitor->acceptColumn($table, $column);
            }
            foreach ($table->getIndexes() as $index) {
                $visitor->acceptIndex($table, $index);
            }
            foreach ($table->getForeignKeys() as $foreignKey) {
                $visitor->acceptForeignKey($table, $foreignKey);
            }
        }

        foreach ($schema->getSequences() as $sequence) {
            $visitor->acceptSequence($sequence);
        }

        return $visitor->getOutput();
    }

    private function buildIgnoreTables(Context $context): array
    {
        $entityManager = $context->getEntityManager();
        $ignoreTables = [];

        if ($context->getIgnoreAll()) {
            foreach ($entityManager->getMetadataFactory()->getAllMetadata() as $metadata) {
                $ignoreTables[] = $metadata->getTableName();
                foreach ($metadata->getAssociationMappings() as $associationMapping) {
                    if (!isset($associationMapping['joinTable'])) {
                        continue;
                    }

                    $ignoreTables[] = $associationMapping['joinTable']['name'];
                }
            }
        }

        $forEntities = $context->getForEntities();
        foreach ($forEntities as $entity) {
            $metadata = $entityManager->getClassMetadata($entity);
            $ignoreTables = array_diff(
                $ignoreTables,
                [$metadata->getTableName()],
            );

            foreach ($metadata->getAssociationMappings() as $associationMapping) {
                if (!isset($associationMapping['joinTable'])) {
                    continue;
                }

                if (!\in_array($associationMapping['targetEntity'], $forEntities, true)) {
                    continue;
                }

                $ignoreTables = array_diff(
                    $ignoreTables,
                    [$associationMapping['joinTable']['name']],
                );
            }
        }

        return array_values($ignoreTables);
    }
}

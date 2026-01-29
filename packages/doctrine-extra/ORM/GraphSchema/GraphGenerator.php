<?php

namespace Draw\DoctrineExtra\ORM\GraphSchema;

use Doctrine\DBAL\Schema\Table;
use Doctrine\DBAL\Types\Type;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Tools\SchemaTool;
use Draw\Component\Graphviz\Edge;
use Draw\Component\Graphviz\Graph;
use Draw\Component\Graphviz\Node;
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

        $graph = new Graph(
            $entityManager->getConnection()->getDatabase() ?? '',
            [
                'splines' => 'true',
                'overlap' => 'false',
                'outputorder' => 'edgesfirst',
                'mindist' => '0.6',
                'sep' => '0.2',
            ]
        );

        $ignoreTables = $this->buildIgnoreTables($context);

        $tool = new SchemaTool($entityManager);
        $schema = $tool->getSchemaFromMetadata($metadata);
        foreach ($schema->getTables() as $table) {
            if (\in_array($table->getObjectName()->toString(), $ignoreTables, true)) {
                continue;
            }

            $graph->addNode(
                new Node(
                    $table->getObjectName()->toString(),
                    [
                        'label' => $this->createTableLabel($table),
                        'shape' => 'plaintext',
                    ]
                )
            );

            $tableName = $table->getObjectName()->toString();
            foreach ($table->getForeignKeys() as $foreignKey) {
                $label = [];
                $onDelete = $foreignKey->getOnDeleteAction()->value;
                $onUpdate = $foreignKey->getOnUpdateAction()->value;
                if ('NO ACTION' !== $onDelete) {
                    $label[] = 'on delete: '.$onDelete;
                }
                if ('NO ACTION' !== $onUpdate) {
                    $label[] = 'on update: '.$onUpdate;
                }
                $localColumn = current($foreignKey->getReferencingColumnNames())->toString();
                $foreignTable = $foreignKey->getReferencedTableName()->toString();
                $foreignColumn = current($foreignKey->getReferencedColumnNames())->toString();
                $graph
                    ->addEdge(
                        new Edge(
                            $tableName.':column_'.$localColumn,
                            $foreignTable.':column_'.$foreignColumn,
                            array_filter([
                                'label' => implode("\n", $label),
                            ]),
                        )
                    )
                ;
            }
        }

        return (string) $graph;
    }

    private function buildIgnoreTables(Context $context): array
    {
        $entityManager = $context->getEntityManager();
        $ignoreTables = [];

        if ($context->getIgnoreAll()) {
            foreach ($entityManager->getMetadataFactory()->getAllMetadata() as $metadata) {
                $ignoreTables[] = $metadata->getTableName();
                foreach ($metadata->getAssociationMappings() as $associationMapping) {
                    if (!isset($associationMapping->joinTable)) {
                        continue;
                    }

                    $ignoreTables[] = $associationMapping->joinTable->name;
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
                if (!isset($associationMapping->joinTable)) {
                    continue;
                }

                if (!\in_array($associationMapping->targetEntity, $forEntities, true)) {
                    continue;
                }

                $ignoreTables = array_diff(
                    $ignoreTables,
                    [$associationMapping->joinTable->name],
                );
            }
        }

        return array_values($ignoreTables);
    }

    private function createTableLabel(Table $table): string
    {
        // Start the table
        $label = <<<TABLE
            <
            <table cellspacing="0" border="1" align="left">
            <tr>
            <td border="1" colspan="3" align="center" bgcolor="#fcaf3e">
            <font color="#2e3436" face="Helvetica">{$table->getObjectName()->toString()}</font>
            </td></tr>
            TABLE;

        // The attributes block
        $primaryKeyColumnNames = array_map(
            static fn ($name) => $name->toString(),
            $table->getPrimaryKeyConstraint()?->getColumnNames() ?? []
        );
        foreach ($table->getColumns() as $column) {
            $columnName = $column->getObjectName()->toString();
            $type = strtolower(Type::getTypeRegistry()->lookupName($column->getType()));
            $primaryKeyMarker = \in_array($columnName, $primaryKeyColumnNames, true)
                ? "\xe2\x9c\xb7"
                : '';

            $label .= <<<TABLE
                <tr>
                <td border="0" align="left" bgcolor="#eeeeec">
                <font color="#2e3436" face="Helvetica">{$columnName}</font>
                </td>
                <td border="0" align="left" bgcolor="#eeeeec">
                <font color="#2e3436" face="Helvetica">{$type}</font>
                </td>
                <td border="0" align="right" bgcolor="#eeeeec" port="column_{$columnName}">{$primaryKeyMarker}</td>
                </tr>
                TABLE;
        }

        // End the table
        $label .= '</table> >';

        return $label;
    }
}

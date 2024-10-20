<?php

namespace Draw\DoctrineExtra\ORM\GraphSchema;

use Doctrine\DBAL\Schema\Table;
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

        $tool = new SchemaTool($entityManager);
        $schema = $tool->getSchemaFromMetadata($metadata);

        $graph = new Graph(
            $schema->getName(),
            [
                'splines' => 'true',
                'overlap' => 'false',
                'outputorder' => 'edgesfirst',
                'mindist' => '0.6',
                'sep' => '0.2',
            ]
        );

        $ignoreTables = $this->buildIgnoreTables($context);

        foreach ($schema->getTables() as $table) {
            if (\in_array($table->getName(), $ignoreTables, true)) {
                continue;
            }

            $graph->addNode(
                new Node(
                    $table->getName(),
                    [
                        'label' => $this->createTableLabel($table),
                        'shape' => 'plaintext',
                    ]
                )
            );

            foreach ($table->getForeignKeys() as $foreignKey) {
                $label = [];
                if ($foreignKey->onDelete()) {
                    $label[] = 'on delete: '.$foreignKey->onDelete();
                }
                if ($foreignKey->onUpdate()) {
                    $label[] = 'on update: '.$foreignKey->onUpdate();
                }
                $graph
                    ->addEdge(
                        new Edge(
                            $foreignKey->getLocalTableName().':column_'.current($foreignKey->getLocalColumns()),
                            $foreignKey->getForeignTableName().':column_'.current($foreignKey->getForeignColumns()),
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

    private function createTableLabel(Table $table): string
    {
        // Start the table
        $label = <<<TABLE
            <
            <table cellspacing="0" border="1" align="left">
            <tr>
            <td border="1" colspan="3" align="center" bgcolor="#fcaf3e">
            <font color="#2e3436" face="Helvetica">{$table->getName()}</font>
            </td></tr>
            TABLE;

        // The attributes block
        foreach ($table->getColumns() as $column) {
            $type = strtolower($column->getType()->getName());
            $primaryKeyMarker = \in_array($column->getName(), $table->getPrimaryKey()?->getColumns() ?? [], true)
                ? "\xe2\x9c\xb7"
                : '';

            $label .= <<<TABLE
                <tr>
                <td border="0" align="left" bgcolor="#eeeeec">
                <font color="#2e3436" face="Helvetica">{$column->getName()}</font>
                </td>
                <td border="0" align="left" bgcolor="#eeeeec">
                <font color="#2e3436" face="Helvetica">{$type}</font>
                </td>
                <td border="0" align="right" bgcolor="#eeeeec" port="column_{$column->getName()}">{$primaryKeyMarker}</td>
                </tr>
                TABLE;
        }

        // End the table
        $label .= '</table> >';

        return $label;
    }
}

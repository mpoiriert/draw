<?php

namespace Draw\DoctrineExtra\ORM\Command;

use Doctrine\DBAL\Schema\Visitor\Graphviz;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Tools\SchemaTool;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class GenerateGraphSchemaCommand extends Command
{
    public function __construct(
        private EntityManagerInterface $entityManager)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setName('draw:doctrine:generate-graph-schema')
            ->setDescription('Get dot from database schema.')
            ->setHelp(\sprintf('Usage: bin/console %s | dot -Tsvg -o /tmp/databse.svg', $this->getName()))
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->writeln($this->getDot());

        return Command::SUCCESS;
    }

    /**
     * Get dot from database schema.
     */
    protected function getDot(): string
    {
        /** @var array<int, ClassMetadata<object>> $metadata */
        $metadata = $this->entityManager->getMetadataFactory()->getAllMetadata();

        usort($metadata, static fn (ClassMetadata $a, ClassMetadata $b): int => $a->getTableName() <=> $b->getTableName());

        $tool = new SchemaTool($this->entityManager);
        $schema = $tool->getSchemaFromMetadata($metadata);

        $visitor = new Graphviz();

        $visitor->acceptSchema($schema);

        foreach ($schema->getTables() as $table) {
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
}

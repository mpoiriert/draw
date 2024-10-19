<?php

namespace Draw\DoctrineExtra\ORM\Command;

use Doctrine\ORM\EntityManagerInterface;
use Draw\DoctrineExtra\ORM\GraphSchema\Context;
use Draw\DoctrineExtra\ORM\GraphSchema\GraphGenerator;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class GenerateGraphSchemaCommand extends Command
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private GraphGenerator $graphGenerator,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setName('draw:doctrine:generate-graph-schema')
            ->addArgument('context-name', InputArgument::OPTIONAL, 'The context name to use.', 'default')
            ->setDescription('Get dot from database schema.')
            ->setHelp(\sprintf('Usage: bin/console %s | dot -Tsvg -o /tmp/databse.svg', $this->getName()))
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->writeln(
            $this->graphGenerator
                ->generate(
                    new Context($this->entityManager, $input->getArgument('context-name'))
                )
        );

        return Command::SUCCESS;
    }
}

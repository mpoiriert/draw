<?php

namespace Draw\Component\EntityMigrator\MessageHandler;

use Draw\Component\EntityMigrator\Message\MigrateEntityCommand;
use Draw\Component\EntityMigrator\Migrator;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class MigrateEntityCommandHandler
{
    public function __construct(private Migrator $migrator)
    {
    }

    public function __invoke(MigrateEntityCommand $command): void
    {
        $this->migrator->migrate($command->getEntity());
    }
}

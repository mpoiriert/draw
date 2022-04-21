<?php

namespace Draw\Component\Tester;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\Console\ConsoleRunner;
use Doctrine\ORM\Tools\Setup;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;

trait DoctrineOrmTrait
{
    /**
     * @param array       $entityDirectories To parse annotations
     * @param string|null $dsn               Will default on getenv('DATABASE_URL')
     */
    protected static function setUpMySqlWithAnnotationDriver(
        array $entityDirectories,
        string $dsn = null
    ): ?EntityManagerInterface {
        $config = Setup::createAnnotationMetadataConfiguration(
            $entityDirectories,
            true,
            null,
            null,
            false
        );

        $entityManager = EntityManager::create(
            [
                'driver' => 'pdo_mysql',
                'url' => $dsn ?: getenv('DATABASE_URL'),
            ],
            $config,
        );

        $helperSet = ConsoleRunner::createHelperSet($entityManager);

        $console = ConsoleRunner::createApplication($helperSet);
        $console->setAutoExit(false);
        $console->setCatchExceptions(false);

        $console->run(
            new ArrayInput([
                'command' => 'orm:schema-tool:update',
                '--force' => null,
            ]),
            new BufferedOutput()
        );

        return $entityManager;
    }
}

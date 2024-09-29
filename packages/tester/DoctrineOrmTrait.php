<?php

namespace Draw\Component\Tester;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\ORMSetup;
use Doctrine\ORM\Tools\Console\ConsoleRunner;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;

trait DoctrineOrmTrait
{
    /**
     * @param array       $entityDirectories To parse annotations
     * @param string|null $dsn               Will default on getenv('DATABASE_URL')
     */
    protected static function setUpMySqlWithAttributeDriver(
        array $entityDirectories,
        ?string $dsn = null,
    ): ?EntityManagerInterface {
        $config = ORMSetup::createAttributeMetadataConfiguration(
            $entityDirectories,
            true
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

    protected static function createRegistry(
        EntityManagerInterface $entityManager,
    ): ManagerRegistry {
        return new class($entityManager) implements ManagerRegistry {
            public function __construct(private EntityManagerInterface $entityManager)
            {
            }

            public function getDefaultConnectionName(): string
            {
                return 'default';
            }

            public function getConnection($name = null)
            {
                return $this->entityManager->getConnection();
            }

            public function getConnections()
            {
                return ['default' => $this->getConnection()];
            }

            public function getConnectionNames()
            {
                return ['default' => 'default'];
            }

            public function getDefaultManagerName()
            {
                return 'default';
            }

            public function getManager($name = null)
            {
                return $this->entityManager;
            }

            public function getManagers()
            {
                return ['default' => $this->entityManager];
            }

            public function resetManager($name = null)
            {
                return $this->entityManager;
            }

            public function getAliasNamespace($alias)
            {
                return $alias;
            }

            public function getManagerNames()
            {
                return ['default' => 'manager.default'];
            }

            public function getRepository($persistentObject, $persistentManagerName = null)
            {
                return $this->entityManager->getRepository($persistentObject);
            }

            public function getManagerForClass($class)
            {
                return $this->entityManager;
            }
        };

    }
}

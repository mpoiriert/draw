<?php

namespace Draw\Component\Tester;

use Doctrine\DBAL\DriverManager;
use Doctrine\DBAL\Tools\DsnParser;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\ORMSetup;
use Doctrine\ORM\Tools\Console\ConsoleRunner;
use Doctrine\ORM\Tools\Console\EntityManagerProvider\SingleManagerProvider;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Persistence\ObjectRepository;
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

        $dsnParser = new DsnParser(['mysql' => 'pdo_mysql']);
        $connection = DriverManager::getConnection(
            $dsnParser->parse($dsn ?: getenv('DATABASE_URL'))
        );

        $entityManager = new EntityManager($connection, $config);

        $console = ConsoleRunner::createApplication(new SingleManagerProvider($entityManager));
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

            public function getConnection(?string $name = null): object
            {
                return $this->entityManager->getConnection();
            }

            public function getConnections(): array
            {
                return ['default' => $this->getConnection()];
            }

            public function getConnectionNames(): array
            {
                return ['default' => 'default'];
            }

            public function getDefaultManagerName(): string
            {
                return 'default';
            }

            public function getManager(?string $name = null): ObjectManager
            {
                return $this->entityManager;
            }

            public function getManagers(): array
            {
                return ['default' => $this->entityManager];
            }

            public function resetManager(?string $name = null): ObjectManager
            {
                return $this->entityManager;
            }

            public function getManagerNames(): array
            {
                return ['default' => 'manager.default'];
            }

            public function getRepository(string $persistentObject, ?string $persistentManagerName = null): ObjectRepository
            {
                return $this->entityManager->getRepository($persistentObject);
            }

            public function getManagerForClass(string $class): ?ObjectManager
            {
                return $this->entityManager;
            }
        };
    }
}

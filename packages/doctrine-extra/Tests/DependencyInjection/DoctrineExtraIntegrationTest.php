<?php

namespace Draw\DoctrineExtra\Tests\DependencyInjection;

use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ORM\EntityManagerInterface;
use Draw\Component\DependencyInjection\Integration\IntegrationInterface;
use Draw\Component\DependencyInjection\Integration\Test\IntegrationTestCase;
use Draw\Component\DependencyInjection\Integration\Test\ServiceConfiguration;
use Draw\DoctrineExtra\DependencyInjection\DoctrineExtraIntegration;
use Draw\DoctrineExtra\ORM\Command\MysqlDumpCommand;
use Draw\DoctrineExtra\ORM\Command\MysqlImportFileCommand;
use Draw\DoctrineExtra\ORM\EntityHandler;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * @property DoctrineExtraIntegration $integration
 *
 * @internal
 */
#[CoversClass(DoctrineExtraIntegration::class)]
class DoctrineExtraIntegrationTest extends IntegrationTestCase
{
    public function createIntegration(): IntegrationInterface
    {
        return new DoctrineExtraIntegration();
    }

    public function getConfigurationSectionName(): string
    {
        return 'doctrine_extra';
    }

    public function getDefaultConfiguration(): array
    {
        return [
            'orm' => [
                'enabled' => ContainerBuilder::willBeAvailable('doctrine/orm', EntityManagerInterface::class, []),
            ],
            'mongodb_odm' => [
                'enabled' => ContainerBuilder::willBeAvailable('doctrine/mongodb-odm', DocumentManager::class, []),
            ],
        ];
    }

    public static function provideTestLoad(): iterable
    {
        yield [
            [
                'doctrine_extra' => [
                    'orm' => [
                        'enabled' => true,
                    ],
                    'mongodb_odm' => [
                        'enabled' => true,
                    ],
                ],
            ],
            [
                new ServiceConfiguration(
                    'draw.doctrine_extra.orm.entity_handler',
                    [
                        EntityHandler::class,
                    ]
                ),
                new ServiceConfiguration(
                    'draw.doctrine_extra.orm.command.mysql_import_file_command',
                    [
                        MysqlImportFileCommand::class,
                    ]
                ),
                new ServiceConfiguration(
                    'draw.doctrine_extra.orm.command.mysql_dump_command',
                    [
                        MysqlDumpCommand::class,
                    ]
                ),
            ],
            [
                'doctrine' => [
                    'Doctrine\Persistence\ManagerRegistry $ormManagerRegistry',
                ],
                'doctrine_mongodb' => [
                    'Doctrine\Persistence\ManagerRegistry $odmManagerRegistry',
                ],
            ],
        ];
    }
}

<?php

namespace Draw\Bundle\FrameworkExtraBundle\Tests\DependencyInjection\Integration;

use Draw\Bundle\FrameworkExtraBundle\DependencyInjection\Integration\DoctrineExtraIntegration;
use Draw\Bundle\FrameworkExtraBundle\DependencyInjection\Integration\IntegrationInterface;
use Draw\DoctrineExtra\ORM\Command\ImportFileCommand;
use Draw\DoctrineExtra\ORM\Command\MysqlDumpCommand;
use Draw\DoctrineExtra\ORM\EntityHandler;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Notifier\NotifierInterface;

/**
 * @property DoctrineExtraIntegration $integration
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
                'enabled' => ContainerBuilder::willBeAvailable('doctrine/orm', NotifierInterface::class, []),
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
                    'draw.doctrine_extra.orm.command.import_file_command',
                    [
                        ImportFileCommand::class,
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

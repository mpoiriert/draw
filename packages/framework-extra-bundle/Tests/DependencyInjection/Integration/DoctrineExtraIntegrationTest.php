<?php

namespace Draw\Bundle\FrameworkExtraBundle\Tests\DependencyInjection\Integration;

use Draw\Bundle\FrameworkExtraBundle\DependencyInjection\Integration\DoctrineExtraIntegration;
use Draw\Bundle\FrameworkExtraBundle\DependencyInjection\Integration\IntegrationInterface;
use Draw\DoctrineExtra\ORM\EntityHandler;
use PHPUnit\Framework\Attributes\CoversClass;

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
        return [];
    }

    public static function provideTestLoad(): iterable
    {
        yield [
            [],
            [
                new ServiceConfiguration(
                    'draw.doctrine_extra.orm.entity_handler',
                    [
                        EntityHandler::class,
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

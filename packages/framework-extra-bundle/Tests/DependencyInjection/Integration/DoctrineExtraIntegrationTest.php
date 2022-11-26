<?php

namespace Draw\Bundle\FrameworkExtraBundle\Tests\DependencyInjection\Integration;

use Draw\Bundle\FrameworkExtraBundle\DependencyInjection\Integration\DoctrineExtraIntegration;
use Draw\Bundle\FrameworkExtraBundle\DependencyInjection\Integration\IntegrationInterface;
use Draw\DoctrineExtra\ORM\EntityHandler;

/**
 * @covers \Draw\Bundle\FrameworkExtraBundle\DependencyInjection\Integration\ConfigurationIntegration
 *
 * @property DoctrineExtraIntegration $integration
 */
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

    public function provideTestLoad(): iterable
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
        ];
    }
}

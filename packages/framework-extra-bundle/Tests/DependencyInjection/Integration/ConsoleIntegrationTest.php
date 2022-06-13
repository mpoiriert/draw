<?php

namespace Draw\Bundle\FrameworkExtraBundle\Tests\DependencyInjection\Integration;

use Doctrine\Bundle\DoctrineBundle\DependencyInjection\DoctrineExtension;
use Draw\Bundle\FrameworkExtraBundle\DependencyInjection\Integration\ConsoleIntegration;
use Draw\Bundle\FrameworkExtraBundle\DependencyInjection\Integration\IntegrationInterface;
use Draw\Component\Console\Command\PurgeExecutionCommand;
use Draw\Component\Console\Entity\Execution;
use Draw\Component\Console\EventListener\CommandFlowListener;
use ReflectionClass;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * @covers \Draw\Bundle\FrameworkExtraBundle\DependencyInjection\Integration\ConsoleIntegration
 *
 * @property ConsoleIntegration $integration
 */
class ConsoleIntegrationTest extends IntegrationTestCase
{
    public function createIntegration(): IntegrationInterface
    {
        return new ConsoleIntegration();
    }

    public function getConfigurationSectionName(): string
    {
        return 'console';
    }

    public function getDefaultConfiguration(): array
    {
        return [];
    }

    public function testPrependNoDoctrineExtension(): void
    {
        static::expectExceptionMessage(
            'You must have the extension [doctrine] available to configuration [draw_framework_extra.console]'
        );

        $this->integration->prepend(
            new ContainerBuilder(),
            []
        );
    }

    public function testPrepend(): void
    {
        $containerBuilder = new ContainerBuilder();
        $containerBuilder->registerExtension(new DoctrineExtension());

        $this->integration->prepend(
            $containerBuilder,
            []
        );

        $reflection = new ReflectionClass(Execution::class);

        static::assertContainerExtensionConfiguration(
            $containerBuilder,
            [
                'doctrine' => [
                    [
                        'orm' => [
                            'mappings' => [
                                'DrawConsole' => [
                                    'is_bundle' => false,
                                    'type' => 'annotation',
                                    'dir' => dirname($reflection->getFileName()),
                                    'prefix' => $reflection->getNamespaceName(),
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        );
    }

    public function provideTestLoad(): iterable
    {
        yield [
            [],
            [
                new ServiceConfiguration(
                    'draw.console.command.purge_execution_command',
                    [
                        PurgeExecutionCommand::class,
                    ]
                ),
                new ServiceConfiguration(
                    'draw.console.event_listener.command_flow_listener',
                    [
                        CommandFlowListener::class,
                    ]
                ),
            ],
        ];
    }
}

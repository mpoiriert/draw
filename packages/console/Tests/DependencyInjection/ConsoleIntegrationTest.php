<?php

namespace Draw\Component\Console\Tests\DependencyInjection;

use Draw\Component\Console\Command\GenerateDocumentationCommand;
use Draw\Component\Console\Command\PurgeExecutionCommand;
use Draw\Component\Console\DependencyInjection\ConsoleIntegration;
use Draw\Component\Console\Descriptor\TextDescriptor;
use Draw\Component\Console\Entity\Execution;
use Draw\Component\Console\EventListener\CommandFlowListener;
use Draw\Component\Console\EventListener\DocumentationFilterCommandEventListener;
use Draw\Component\DependencyInjection\Integration\IntegrationInterface;
use Draw\Component\DependencyInjection\Integration\Test\IntegrationTestCase;
use Draw\Component\DependencyInjection\Integration\Test\ServiceConfiguration;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Component\Console\Helper\DescriptorHelper;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;

/**
 * @property ConsoleIntegration $integration
 *
 * @internal
 */
#[CoversClass(ConsoleIntegration::class)]
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
        return [
            'ignore_disabled_command' => false,
            'documentation' => [
                'filter' => 'in',
                'command_names' => [],
            ],
        ];
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
        $containerBuilder->registerExtension($this->mockExtension('doctrine'));

        $this->integration->prepend(
            $containerBuilder,
            []
        );

        $reflection = new \ReflectionClass(Execution::class);

        static::assertContainerExtensionConfiguration(
            $containerBuilder,
            [
                'doctrine' => [
                    [
                        'orm' => [
                            'mappings' => [
                                'DrawConsole' => [
                                    'is_bundle' => false,
                                    'type' => 'attribute',
                                    'dir' => \dirname($reflection->getFileName()),
                                    'prefix' => $reflection->getNamespaceName(),
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        );
    }

    public static function provideLoadCases(): iterable
    {
        yield [
            [
                [
                    'ignore_disabled_command' => true,
                    'documentation' => [
                        'filter' => 'out',
                        'command_names' => [
                            'help',
                        ],
                    ],
                ],
            ],
            [
                new ServiceConfiguration(
                    'draw.console.command.generate_documentation_command',
                    [
                        GenerateDocumentationCommand::class,
                    ],
                ),
                new ServiceConfiguration(
                    'draw.console.command.purge_execution_command',
                    [
                        PurgeExecutionCommand::class,
                    ]
                ),
                new ServiceConfiguration(
                    'draw.console.descriptor.text_descriptor',
                    [
                        TextDescriptor::class,
                    ]
                ),
                new ServiceConfiguration(
                    'draw.console.descriptor_helper',
                    [
                        DescriptorHelper::class,
                    ]
                ),
                new ServiceConfiguration(
                    'draw.console.event_listener.command_flow_listener',
                    [
                        CommandFlowListener::class,
                    ],
                    static function (Definition $definition): void {
                        static::assertTrue(
                            $definition->getArgument('$ignoreDisabledCommand')
                        );
                    }
                ),
                new ServiceConfiguration(
                    'draw.console.event_listener.documentation_filter_command_event_listener',
                    [
                        DocumentationFilterCommandEventListener::class,
                    ],
                    static function (Definition $definition): void {
                        static::assertSame(
                            'out',
                            $definition->getArgument('$filter')
                        );
                        static::assertSame(
                            ['help'],
                            $definition->getArgument('$commandNames')
                        );
                    }
                ),
            ],
        ];
    }
}

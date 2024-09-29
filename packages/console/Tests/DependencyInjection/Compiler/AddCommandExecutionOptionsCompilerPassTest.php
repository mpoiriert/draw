<?php

namespace Draw\Component\Console\Tests\DependencyInjection\Compiler;

use Draw\Component\Console\DependencyInjection\Compiler\AddCommandExecutionOptionsCompilerPass;
use Draw\Component\Console\EventListener\CommandFlowListener;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;

/**
 * @internal
 */
#[CoversClass(AddCommandExecutionOptionsCompilerPass::class)]
class AddCommandExecutionOptionsCompilerPassTest extends TestCase
{
    private AddCommandExecutionOptionsCompilerPass $compilerPass;

    private ContainerBuilder $containerBuilder;

    protected function setUp(): void
    {
        $this->compilerPass = new AddCommandExecutionOptionsCompilerPass();
        $this->containerBuilder = new ContainerBuilder();
    }

    public function testProcessNoDefinition(): void
    {
        $definition = new Definition(\stdClass::class);

        $this->containerBuilder->setDefinition('service-id', $definition)->addTag('console.command');

        $this->compilerPass->process($this->containerBuilder);

        static::assertSame(
            [],
            $definition->getMethodCalls()
        );
    }

    public function testProcess(): void
    {
        $this->containerBuilder->setDefinition(CommandFlowListener::class, new Definition(\stdClass::class));

        $definition = new Definition(\stdClass::class);

        $this->containerBuilder->setDefinition('service-id', $definition)->addTag('console.command');

        $this->compilerPass->process($this->containerBuilder);

        static::assertSame(
            [
                [
                    'addOption',
                    [
                        CommandFlowListener::OPTION_EXECUTION_ID,
                        null,
                        InputOption::VALUE_REQUIRED,
                        'The existing execution id of the command. Use internally by the DrawCommandBundle.',
                    ],
                ],
                [
                    'addOption',
                    [
                        CommandFlowListener::OPTION_IGNORE,
                        null,
                        InputOption::VALUE_NONE,
                        'Flag to ignore login of the execution to the databases.',
                    ],
                ],
            ],
            $definition->getMethodCalls()
        );
    }
}

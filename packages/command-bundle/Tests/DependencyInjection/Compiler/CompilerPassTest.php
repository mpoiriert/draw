<?php

namespace Draw\Bundle\CommandBundle\Tests\DependencyInjection\Compiler;

use Draw\Bundle\CommandBundle\DependencyInjection\Compiler\CompilerPass;
use Draw\Bundle\CommandBundle\Listener\CommandFlowListener;
use PHPUnit\Framework\TestCase;
use stdClass;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;

class CompilerPassTest extends TestCase
{
    private $compilerPass;

    private $containerBuilder;

    public function setUp(): void
    {
        $this->compilerPass = new CompilerPass();
        $this->containerBuilder = new ContainerBuilder();
    }

    public function testProcess(): void
    {
        $definition = new Definition(stdClass::class);

        $this->containerBuilder->setDefinition('service-id', $definition)->addTag('console.command');

        $this->compilerPass->process($this->containerBuilder);

        $this->assertSame(
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
                        InputOption::VALUE_OPTIONAL,
                        'Flag to ignore login of the execution to the databases.',
                    ],
                ],
            ],
            $definition->getMethodCalls()
        );
    }
}

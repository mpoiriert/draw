<?php

namespace Draw\Component\AwsToolKit\Tests\DependencyInjection\Compiler;

use Draw\Component\AwsToolKit\DependencyInjection\Compiler\AddNewestInstanceRoleCommandOptionPass;
use Draw\Component\AwsToolKit\EventListener\NewestInstanceRoleCheckListener;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;

#[CoversClass(AddNewestInstanceRoleCommandOptionPass::class)]
class AddNewestInstanceRoleCommandOptionPassTest extends TestCase
{
    private AddNewestInstanceRoleCommandOptionPass $compilerPass;

    private ContainerBuilder $containerBuilder;

    protected function setUp(): void
    {
        $this->compilerPass = new AddNewestInstanceRoleCommandOptionPass();
        $this->containerBuilder = new ContainerBuilder();
    }

    public function testProcessNoNewestInstanceRoleCheckListener(): void
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
        $definition = new Definition(\stdClass::class);

        $this->containerBuilder->setDefinition(NewestInstanceRoleCheckListener::class, clone $definition);
        $this->containerBuilder->setDefinition('service-id', $definition)->addTag('console.command');

        $this->compilerPass->process($this->containerBuilder);

        static::assertSame(
            [
                [
                    'addOption',
                    [
                        NewestInstanceRoleCheckListener::OPTION_AWS_NEWEST_INSTANCE_ROLE,
                        null,
                        InputOption::VALUE_REQUIRED,
                        'The instance role the server must be the newest of to run the command.',
                    ],
                ],
            ],
            $definition->getMethodCalls()
        );
    }
}

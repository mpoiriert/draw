<?php

namespace Draw\Bundle\AwsToolKitBundle\Tests\DependencyInjection\Compiler;

use Draw\Bundle\AwsToolKitBundle\DependencyInjection\Compiler\CompilerPass;
use Draw\Bundle\AwsToolKitBundle\Listener\NewestInstanceRoleListener;
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
                        NewestInstanceRoleListener::OPTION_AWS_NEWEST_INSTANCE_ROLE,
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

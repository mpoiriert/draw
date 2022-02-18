<?php

namespace Draw\Bundle\TesterBundle\Tests\DependencyInjection\Compiler;

use Draw\Bundle\TesterBundle\DependencyInjection\Compiler\MessengerPass;
use Draw\Bundle\TesterBundle\Messenger\HandleMessagesMappingProvider;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;

/**
 * @covers \Draw\Bundle\TesterBundle\DependencyInjection\Compiler\MessengerPass
 */
class MessengerPassTest extends TestCase
{
    private $compilerPass;

    public function setUp(): void
    {
        $this->compilerPass = new MessengerPass();
    }

    public function testProcess(): void
    {
        $containerBuilder = new ContainerBuilder();
        $containerBuilder->setDefinition(
            'console.command.messenger_debug',
            new Definition()
        )->setArgument(
            0,
            $argument = [
                'test.bus' => [
                    \stdClass::class => [
                        [\stdClass::class, []],
                    ],
                ],
            ],
        );

        $containerBuilder->setDefinition(
            HandleMessagesMappingProvider::class,
            $definition = new Definition()
        );

        $this->compilerPass->process($containerBuilder);

        $this->assertSame(
            $argument,
            $definition->getArgument(0)
        );
    }
}

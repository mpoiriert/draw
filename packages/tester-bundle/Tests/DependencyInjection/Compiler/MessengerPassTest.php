<?php

namespace Draw\Bundle\TesterBundle\Tests\DependencyInjection\Compiler;

use Draw\Bundle\TesterBundle\DependencyInjection\Compiler\MessengerPass;
use Draw\Bundle\TesterBundle\Messenger\HandleMessagesMappingProvider;
use Draw\Bundle\TesterBundle\Messenger\HandlerConfigurationDumper;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;

/**
 * @internal
 */
#[CoversClass(MessengerPass::class)]
class MessengerPassTest extends TestCase
{
    private MessengerPass $compilerPass;

    protected function setUp(): void
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
            $handleMessagesMappingProviderDefinition = new Definition()
        );

        $containerBuilder->setDefinition(
            HandlerConfigurationDumper::class,
            $handlerConfigurationDumperDefinition = new Definition()
        );

        $this->compilerPass->process($containerBuilder);

        static::assertSame(
            $argument,
            $handleMessagesMappingProviderDefinition->getArgument(0)
        );

        static::assertSame(
            $argument,
            $handlerConfigurationDumperDefinition->getArgument(0)
        );
    }
}

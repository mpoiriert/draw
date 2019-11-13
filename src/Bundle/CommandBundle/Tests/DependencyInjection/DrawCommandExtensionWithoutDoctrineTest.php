<?php namespace Draw\Bundle\CommandBundle\Tests\DependencyInjection;

use Draw\Bundle\CommandBundle\Listener\CommandFlowListener;

class DrawCommandExtensionWithoutDoctrineTest extends DrawCommandExtensionTest
{
    public function getConfiguration(): array
    {
        return ['doctrine' => ['enabled' => false]];
    }

    public function provideTestHasServiceDefinition(): iterable
    {
        yield from $this->removeProvidedService(
            [CommandFlowListener::class],
            parent::provideTestHasServiceDefinition()
        );
    }
}
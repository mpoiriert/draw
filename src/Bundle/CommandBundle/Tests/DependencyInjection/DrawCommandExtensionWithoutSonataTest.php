<?php namespace Draw\Bundle\CommandBundle\Tests\DependencyInjection;

use Draw\Bundle\CommandBundle\Sonata\Admin\ExecutionAdmin;
use Draw\Bundle\CommandBundle\Sonata\Controller\ExecutionController;

class DrawCommandExtensionWithoutSonataTest extends DrawCommandExtensionTest
{
    public function getConfiguration(): array
    {
        return ['sonata' => false];
    }

    public function provideTestHasServiceDefinition(): iterable
    {
        yield from $this->removeProvidedService(
            [
                ExecutionAdmin::class,
                ExecutionController::class
            ],
            parent::provideTestHasServiceDefinition()
        );
    }
}
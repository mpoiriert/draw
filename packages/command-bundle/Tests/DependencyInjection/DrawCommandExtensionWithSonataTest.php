<?php

namespace Draw\Bundle\CommandBundle\Tests\DependencyInjection;

use Draw\Bundle\CommandBundle\Sonata\Admin\ExecutionAdmin;
use Draw\Bundle\CommandBundle\Sonata\Controller\ExecutionController;

class DrawCommandExtensionWithSonataTest extends DrawCommandExtensionTest
{
    public function getConfiguration(): array
    {
        return ['sonata' => true];
    }

    public function provideTestHasServiceDefinition(): iterable
    {
        yield from parent::provideTestHasServiceDefinition();
        yield [ExecutionAdmin::class];
        yield [ExecutionController::class];
    }
}

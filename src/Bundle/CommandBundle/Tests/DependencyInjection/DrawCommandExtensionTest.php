<?php

namespace Draw\Bundle\CommandBundle\Tests\DependencyInjection;

use Draw\Bundle\CommandBundle\Command\PurgeExecutionCommand;
use Draw\Bundle\CommandBundle\CommandRegistry;
use Draw\Bundle\CommandBundle\DependencyInjection\DrawCommandExtension;
use Draw\Bundle\CommandBundle\Listener\CommandFlowListener;
use Draw\Bundle\CommandBundle\Sonata\Admin\ExecutionAdmin;
use Draw\Bundle\CommandBundle\Sonata\Controller\ExecutionController;
use Draw\Component\Tester\DependencyInjection\ExtensionTestCase;
use Symfony\Component\DependencyInjection\Extension\Extension;

class DrawCommandExtensionTest extends ExtensionTestCase
{
    public function createExtension(): Extension
    {
        return new DrawCommandExtension();
    }

    public function getConfiguration(): array
    {
        return [];
    }

    public function provideTestHasServiceDefinition(): iterable
    {
        yield [CommandRegistry::class];
        yield [PurgeExecutionCommand::class];
        yield [CommandFlowListener::class];
        yield [ExecutionAdmin::class];
        yield [ExecutionController::class];
    }
}

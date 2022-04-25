<?php

namespace Draw\Bundle\FrameworkExtraBundle\Tests\DependencyInjection;

use Draw\Component\Console\Command\PurgeExecutionCommand;
use Draw\Component\Console\Listener\CommandFlowListener;

class DrawFrameworkExtraExtensionConsoleEnabledTest extends DrawFrameworkExtraExtensionTest
{
    public function getConfiguration(): array
    {
        $configuration = parent::getConfiguration();

        $configuration['console'] = [
            'enabled' => true,
        ];

        return $configuration;
    }

    public function provideTestHasServiceDefinition(): iterable
    {
        yield from parent::provideTestHasServiceDefinition();
        yield ['draw.console.command.purge_execution'];
        yield [PurgeExecutionCommand::class, 'draw.console.command.purge_execution'];
        yield ['draw.console.command_flow_listener'];
        yield [CommandFlowListener::class, 'draw.console.command_flow_listener'];
    }
}

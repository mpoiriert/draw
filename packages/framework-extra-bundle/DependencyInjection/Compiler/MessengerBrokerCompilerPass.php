<?php

namespace Draw\Bundle\FrameworkExtraBundle\DependencyInjection\Compiler;

use Draw\Component\Messenger\Broker\Command\StartMessengerBrokerCommand;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class MessengerBrokerCompilerPass implements CompilerPassInterface
{
    use ConsolePathAwareCompilerPassTrait;

    public function process(ContainerBuilder $container): void
    {
        $this->setConsolePathArgument($container, StartMessengerBrokerCommand::class);
    }
}

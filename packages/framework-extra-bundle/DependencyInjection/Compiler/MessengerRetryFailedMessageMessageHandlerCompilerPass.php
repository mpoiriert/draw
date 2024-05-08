<?php

declare(strict_types=1);

namespace Draw\Bundle\FrameworkExtraBundle\DependencyInjection\Compiler;

use Draw\Component\Messenger\MessageHandler\RetryFailedMessageMessageHandler;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class MessengerRetryFailedMessageMessageHandlerCompilerPass implements CompilerPassInterface
{
    use ConsolePathAwareCompilerPassTrait;

    public function process(ContainerBuilder $container): void
    {
        $this->setConsolePathArgument($container, RetryFailedMessageMessageHandler::class);
    }
}

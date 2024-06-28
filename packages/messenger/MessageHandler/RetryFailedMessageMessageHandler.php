<?php

declare(strict_types=1);

namespace Draw\Component\Messenger\MessageHandler;

use Draw\Component\Messenger\Message\RetryFailedMessageMessage;
use Draw\Contracts\Process\ProcessFactoryInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

class RetryFailedMessageMessageHandler
{
    public function __construct(
        private ProcessFactoryInterface $processFactory,
        #[Autowire('%draw.symfony_console_path%')]
        private string $consolePath,
    ) {
    }

    #[AsMessageHandler]
    public function handleRetryFailedMessageMessage(RetryFailedMessageMessage $message): void
    {
        $this->processFactory
            ->create(
                [
                    $this->consolePath,
                    'messenger:failed:retry',
                    $message->getMessageId(),
                    '--force',
                ]
            )
            ->mustRun();
    }
}

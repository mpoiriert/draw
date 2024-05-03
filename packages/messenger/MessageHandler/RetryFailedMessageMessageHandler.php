<?php

declare(strict_types=1);

namespace Draw\Component\Messenger\MessageHandler;

use Draw\Component\Messenger\Message\RetryFailedMessageMessage;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

class RetryFailedMessageMessageHandler
{
    public function __construct(
        private KernelInterface $kernel,
    ) {
    }

    #[AsMessageHandler]
    public function handleRetryFailedMessageMessage(RetryFailedMessageMessage $message): void
    {
        $application = new Application($this->kernel);
        $application->setAutoExit(false);

        $application->run(
            new ArrayInput(
                [
                    'command' => 'messenger:failed:retry',
                    'id' => [
                        $message->getMessage()->getId(),
                    ],
                    '--force' => true,
                ]
            ),
            new NullOutput()
        );
    }
}

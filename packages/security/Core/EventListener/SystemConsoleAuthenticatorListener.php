<?php

namespace Draw\Component\Security\Core\EventListener;

use Draw\Component\Security\Core\Authentication\SystemAuthenticatorInterface;
use Symfony\Component\Console\Event\ConsoleCommandEvent;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class SystemConsoleAuthenticatorListener implements EventSubscriberInterface
{
    final public const OPTION_AS_SYSTEM = 'as-system';

    public static function getSubscribedEvents(): array
    {
        return [
            ConsoleCommandEvent::class => [
                ['addOptions', 255],
                ['connectSystem', 0],
            ],
        ];
    }

    public function __construct(
        private TokenStorageInterface $tokenStorage,
        private SystemAuthenticatorInterface $systemAuthenticator,
        private bool $systemAutoLogin
    ) {
    }

    public function addOptions(ConsoleCommandEvent $consoleCommandEvent): void
    {
        $consoleCommandEvent
            ->getCommand()
            ->addOption(
                self::OPTION_AS_SYSTEM,
                null,
                InputOption::VALUE_NONE,
                'Execute the current command connected as the system user.',
            );
    }

    public function connectSystem(ConsoleCommandEvent $consoleCommandEvent): void
    {
        $input = $consoleCommandEvent->getInput();

        $input->bind($consoleCommandEvent->getCommand()->getDefinition());

        $connect = $this->systemAutoLogin
            || (
                $input->hasOption(self::OPTION_AS_SYSTEM)
                && $input->getOption(self::OPTION_AS_SYSTEM)
            );

        if (!$connect) {
            return;
        }

        if ($this->tokenStorage->getToken()) {
            return;
        }

        $this->tokenStorage->setToken($this->systemAuthenticator->getTokenForSystem());
    }
}

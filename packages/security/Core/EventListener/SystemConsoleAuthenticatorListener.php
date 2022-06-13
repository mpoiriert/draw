<?php

namespace Draw\Component\Security\Core\EventListener;

use Draw\Component\Security\Core\Authentication\SystemAuthenticatorInterface;
use Symfony\Component\Console\Event\ConsoleCommandEvent;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class SystemConsoleAuthenticatorListener implements EventSubscriberInterface
{
    public const OPTION_AS_SYSTEM = 'as-system';

    private SystemAuthenticatorInterface $systemAuthenticator;

    private bool $systemAutoLogin;

    private TokenStorageInterface $tokenStorage;

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
        TokenStorageInterface $tokenStorage,
        SystemAuthenticatorInterface $systemAuthenticator,
        bool $systemAutoLogin
    ) {
        $this->systemAuthenticator = $systemAuthenticator;
        $this->tokenStorage = $tokenStorage;
        $this->systemAutoLogin = $systemAutoLogin;
    }

    public function addOptions(ConsoleCommandEvent $consoleCommandEvent): void
    {
        $consoleCommandEvent
            ->getCommand()
            ->addOption(
                self::OPTION_AS_SYSTEM,
                null,
                InputOption::VALUE_OPTIONAL,
                'Execute the current command connected as the system user.',
                $this->systemAutoLogin
            );
    }

    public function connectSystem(ConsoleCommandEvent $consoleCommandEvent): void
    {
        $consoleCommandEvent->getInput()->bind($consoleCommandEvent->getCommand()->getDefinition());

        if (!$consoleCommandEvent->getInput()->hasOption(self::OPTION_AS_SYSTEM)) {
            return;
        }

        $this->tokenStorage->setToken($this->systemAuthenticator->getTokenForSystem());
    }
}

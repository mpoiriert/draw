<?php

namespace Draw\Bundle\CommandBundle\Authentication\Listener;

use Draw\Bundle\CommandBundle\Authentication\SystemAuthenticatorInterface;
use Symfony\Component\Console\Event\ConsoleCommandEvent;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class CommandLineAuthenticatorListener implements EventSubscriberInterface
{
    public const OPTION_AS_SYSTEM = 'as-system';

    /**
     * @var SystemAuthenticatorInterface
     */
    private $systemAuthenticator;

    /**
     * @var bool
     */
    private $systemAutoLogin;

    /**
     * @var TokenStorageInterface
     */
    private $tokenStorage;

    public static function getSubscribedEvents()
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

    /**
     * This is a fallback on the compiler pass system to be sure options are available if command are registered
     * by another mean.
     */
    public function addOptions(ConsoleCommandEvent $consoleCommandEvent)
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

    /**
     * @return void
     */
    public function connectSystem(ConsoleCommandEvent $consoleCommandEvent)
    {
        $consoleCommandEvent->getInput()->bind($consoleCommandEvent->getCommand()->getDefinition());

        if (!$consoleCommandEvent->getInput()->getOption(self::OPTION_AS_SYSTEM)) {
            return;
        }

        $this->tokenStorage->setToken($this->systemAuthenticator->getTokenForSystem());
    }
}

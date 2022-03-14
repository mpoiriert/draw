<?php

namespace Draw\Bundle\ApplicationBundle\Versioning;

use Draw\Bundle\ApplicationBundle\Versioning\Event\FetchRunningVersionEvent;
use Draw\Contracts\Application\ConfigurationRegistryInterface;
use Draw\Contracts\Application\VersionVerificationInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class VersionManager implements VersionVerificationInterface
{
    public const CONFIG = 'draw-application-deployed-version';

    /**
     * @var string
     */
    private $runningVersion;

    private $eventDispatcher;

    /**
     * @var ConfigurationRegistryInterface
     */
    private $configurationRegistry;

    public function __construct(
        ConfigurationRegistryInterface $configurationRegistry,
        EventDispatcherInterface $eventDispatcher
    ) {
        $this->configurationRegistry = $configurationRegistry;
        $this->eventDispatcher = $eventDispatcher;
    }

    public function getRunningVersion(): ?string
    {
        if (null === $this->runningVersion) {
            $this->runningVersion = '';

            $this->eventDispatcher->dispatch($event = new FetchRunningVersionEvent());

            $this->runningVersion = $event->getRunningVersion() ?: '';
        }

        return $this->runningVersion ?: null;
    }

    public function updateDeployedVersion(): void
    {
        $this->configurationRegistry->set(static::CONFIG, $this->getRunningVersion());
    }

    public function getDeployedVersion(): ?string
    {
        return $this->configurationRegistry->get(static::CONFIG);
    }

    public function isUpToDate(): bool
    {
        return $this->getDeployedVersion() === $this->getRunningVersion();
    }
}

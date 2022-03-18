<?php

namespace Draw\Bundle\ApplicationBundle\Tests\DependencyInjection;

use Draw\Bundle\ApplicationBundle\DependencyInjection\DrawApplicationExtension;
use Draw\Bundle\ApplicationBundle\Versioning\Command\ApplicationVersionUpdateDeployedVersionCommand;
use Draw\Bundle\ApplicationBundle\Versioning\Listener\FetchRunningVersionListener;
use Draw\Bundle\ApplicationBundle\Versioning\VersionManager;
use Draw\Contracts\Application\VersionVerificationInterface;
use Symfony\Component\DependencyInjection\Extension\Extension;

class DrawApplicationExtensionVersioningEnabledTest extends DrawApplicationExtensionTest
{
    public function createExtension(): Extension
    {
        return new DrawApplicationExtension();
    }

    public function getConfiguration(): array
    {
        return [
            'versioning' => [
                'enabled' => true,
            ],
        ];
    }

    public function provideTestHasServiceDefinition(): iterable
    {
        yield from parent::provideTestHasServiceDefinition();
        yield [ApplicationVersionUpdateDeployedVersionCommand::class];
        yield [FetchRunningVersionListener::class];
        yield [VersionManager::class];
        yield [VersionVerificationInterface::class, VersionManager::class];
    }
}

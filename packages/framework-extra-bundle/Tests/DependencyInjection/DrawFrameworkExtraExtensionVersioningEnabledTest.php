<?php

namespace Draw\Bundle\FrameworkExtraBundle\Tests\DependencyInjection;

use Draw\Component\Application\Command\UpdateDeployedVersionCommand;
use Draw\Component\Application\Listener\FetchRunningVersionListener;
use Draw\Component\Application\VersionManager;
use Draw\Contracts\Application\VersionVerificationInterface;

class DrawFrameworkExtraExtensionVersioningEnabledTest extends DrawFrameworkExtraExtensionTest
{
    public function getConfiguration(): array
    {
        $configuration = parent::getConfiguration();

        $configuration['versioning'] = [
            'enabled' => true,
        ];

        return $configuration;
    }

    public function provideTestHasServiceDefinition(): iterable
    {
        yield from parent::provideTestHasServiceDefinition();
        yield ['draw.versioning.command.update_deployed_version'];
        yield [UpdateDeployedVersionCommand::class, 'draw.versioning.command.update_deployed_version'];
        yield ['draw.versioning.fetch_running_version_listener'];
        yield [FetchRunningVersionListener::class, 'draw.versioning.fetch_running_version_listener'];
        yield ['draw.versioning.version_manager'];
        yield [VersionManager::class, 'draw.versioning.version_manager'];
        yield [VersionVerificationInterface::class, 'draw.versioning.version_manager'];
    }
}

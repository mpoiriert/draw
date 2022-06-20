<?php

use Composer\InstalledVersions;
use Composer\Semver\VersionParser;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    if (InstalledVersions::satisfies(new VersionParser(), 'sonata-project/admin-bundle', '^3.105')) {
        $containerConfigurator->import('sonata_legacy.yaml');
    }
};

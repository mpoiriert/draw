<?php

declare(strict_types=1);

use Draw\Development\MonorepoBuilder\Release\ReleaseWorker as DrawReleaseWorker;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symplify\MonorepoBuilder\Release\ReleaseWorker;
use Symplify\MonorepoBuilder\Split\ValueObject\ConvertFormat;
use Symplify\MonorepoBuilder\ValueObject\Option;

return static function (ContainerConfigurator $containerConfigurator): void {
    $parameters = $containerConfigurator->parameters();

    // where are the packages located?
    $parameters->set(Option::PACKAGE_DIRECTORIES, [
        __DIR__.'/src/Bundle',
        __DIR__.'/src/Component',
    ]);

    // "merge" command related

    // what extra parts to add after merge?
    $parameters->set(Option::DATA_TO_APPEND, [
        'require-dev' => [
            'bamarni/composer-bin-plugin' => '^1.4',
            'nelmio/cors-bundle' => '^2.0',
            'phpunit/phpunit' => '^7.0',
            'doctrine/doctrine-fixtures-bundle' => '^3.2',
            'kunicmarko/sonata-auto-configure-bundle' => '^0.7.1',
            'pelago/emogrifier' => '^3.0',
            'ramsey/uuid' => '^3.8',
            'sonata-project/admin-bundle' => '^3.54',
            'sonata-project/doctrine-orm-admin-bundle' => '^3.10',
            'symfony/debug-bundle' => '^4.4',
            'symfony/dotenv' => '^4.4',
            'symfony/web-profiler-bundle' => '^4.4',
            'symfony/stopwatch' => '^4.4',
            'symplify/monorepo-builder' => '^8.3.48',
        ],
    ]);

    // How to split them on github ?

    $parameters->set(Option::DIRECTORIES_TO_REPOSITORIES, [
        __DIR__.'/src/Component/*' => 'git@github.com:mpoiriert/*.git',
        __DIR__.'/src/Bundle/*' => 'git@github.com:mpoiriert/*.git',
    ]);

    $parameters->set(Option::DIRECTORIES_TO_REPOSITORIES_CONVERT_FORMAT, ConvertFormat::PASCAL_CASE_TO_KEBAB_CASE);

    // What is the release workflow

    $services = $containerConfigurator->services();

    // release workers - in order to execute
    $services->set(ReleaseWorker\SetCurrentMutualDependenciesReleaseWorker::class);
    $services->set(ReleaseWorker\AddTagToChangelogReleaseWorker::class);
    $services->set(DrawReleaseWorker\TagCurrentBranchReleaseWorker::class);
    $services->set(ReleaseWorker\PushTagReleaseWorker::class);
    $services->set(ReleaseWorker\SetNextMutualDependenciesReleaseWorker::class);
    $services->set(ReleaseWorker\UpdateBranchAliasReleaseWorker::class);
    $services->set(DrawReleaseWorker\PushNextDevReleaseWorker::class);
};

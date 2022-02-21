<?php

declare(strict_types=1);

require_once __DIR__.'/vendor/autoload.php';

use Draw\Development\MonorepoBuilder\Release\ReleaseWorker as DrawReleaseWorker;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symplify\MonorepoBuilder\Release\ReleaseWorker;
use Symplify\MonorepoBuilder\ValueObject\Option;

return static function (ContainerConfigurator $containerConfigurator): void {
    $parameters = $containerConfigurator->parameters();

    // where are the packages located?
    $parameters->set(Option::PACKAGE_DIRECTORIES, [
        __DIR__.'/packages',
    ]);

    // "merge" command related

    // what extra parts to add after merge?
    $parameters->set(Option::DATA_TO_APPEND, [
        'require-dev' => [
            'aws/aws-sdk-php-symfony' => '^2.2',
            'bamarni/composer-bin-plugin' => '^1.4',
            'composer/semver' => '^3.2',
            'doctrine/doctrine-migrations-bundle' => '^3.1',
            'doctrine/doctrine-fixtures-bundle' => '^3.4',
            'doctrine/sql-formatter' => '^1.1',
            'nelmio/cors-bundle' => '^2.0',
            'pelago/emogrifier' => '^6.0',
            'phpunit/phpunit' => '^8.0|^9.0',
            'sonata-project/admin-bundle' => '^3.105|^4.0',
            'sonata-project/doctrine-orm-admin-bundle' => '^3.35|^4.0',
            'symfony/debug-bundle' => '^4.4|^5.3',
            'symfony/dotenv' => '^4.4|^5.3',
            'symfony/flex' => '^1.16',
            'symfony/form' => '^4.4|^5.3',
            'symfony/monolog-bundle' => '^3.7',
            'symfony/web-profiler-bundle' => '^4.4|^5.3',
            'symfony/stopwatch' => '^4.4|^5.3',
        ],
    ]);

    $parameters->set(Option::DATA_TO_APPEND, [
        'conflict' => [
            'symfony/security-bundle' => '<4.4.1',
        ],
    ]);

    // How to split them on github ?

    $parameters->set(Option::DIRECTORIES_TO_REPOSITORIES, [
        __DIR__.'/packages/*' => 'git@github.com:mpoiriert/*.git',
    ]);

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

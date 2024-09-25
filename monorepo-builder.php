<?php

declare(strict_types=1);

require_once __DIR__.'/vendor-bin/monorepo/vendor/autoload.php';

use Draw\Development\MonorepoBuilder\Release\ReleaseWorker as DrawReleaseWorker;
use Symplify\MonorepoBuilder\Config\MBConfig;
use Symplify\MonorepoBuilder\Release\ReleaseWorker;

return static function (MBConfig $mbConfig): void {
    $mbConfig::disableDefaultWorkers();

    $mbConfig->packageDirectories([
        __DIR__.'/packages',
    ]);

    $mbConfig->dataToAppend([
        'require-dev' => [
            'aws/aws-sdk-php-symfony' => '^2.2',
            'bamarni/composer-bin-plugin' => '^1.4',
            'composer/semver' => '^3.2',
            'doctrine/doctrine-migrations-bundle' => '^3.1',
            'doctrine/doctrine-fixtures-bundle' => '^3.5',
            'doctrine/sql-formatter' => '^1.1',
            'nelmio/cors-bundle' => '^2.0',
            'pelago/emogrifier' => '^7.0',
            'phpunit/phpunit' => '^11.3',
            'sonata-project/admin-bundle' => '^4.8',
            'sonata-project/doctrine-orm-admin-bundle' => '^4.2',
            'symfony/debug-bundle' => '^6.4.0',
            'symfony/dotenv' => '^6.4.0',
            'symfony/flex' => '^1.16',
            'symfony/form' => '^6.4.0',
            'symfony/monolog-bundle' => '^3.7',
            'symfony/web-profiler-bundle' => '^6.4.0',
            'symfony/stopwatch' => '^6.4.0',
        ],
    ]);

    // What is the release workflow

    $mbConfig->isDisableDefaultWorkers();

    $mbConfig->workers([
        ReleaseWorker\SetCurrentMutualDependenciesReleaseWorker::class,
        DrawReleaseWorker\TagCurrentBranchReleaseWorker::class,
        ReleaseWorker\PushTagReleaseWorker::class,
        ReleaseWorker\SetNextMutualDependenciesReleaseWorker::class,
        ReleaseWorker\UpdateBranchAliasReleaseWorker::class,
        DrawReleaseWorker\PushNextDevReleaseWorker::class,
    ]);
};

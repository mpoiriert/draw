<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;

return RectorConfig::configure()
    ->withPaths([
        __DIR__.'/app',
        __DIR__.'/config',
        __DIR__.'/packages',
        __DIR__.'/public',
        __DIR__.'/tests',
    ])
    ->withPhpSets()
    ->withComposerBased(
        twig: true,
        doctrine: true,
        phpunit: true,
        symfony: true,
        netteUtils: true,
    )
    ->withAttributesSets(
        symfony: true,
        doctrine: true,
        gedmo: true,
        phpunit: true,
        jms: true,
        sensiolabs: true,
    )
    ->withPreparedSets(
        deadCode: true,
        privatization: true,
        symfonyCodeQuality: true,
        symfonyConfigs: true,
    )
    ->withSkip([
        // PHP
        Rector\Php80\Rector\Class_\ClassPropertyAssignToConstructorPromotionRector::class,
        Rector\Php81\Rector\Property\ReadOnlyPropertyRector::class,
        Rector\Php81\Rector\Array_\ArrayToFirstClassCallableRector::class => [
            __DIR__.'/packages/mailer/Twig/TranslationExtension.php',
            __DIR__.'/packages/mailer/Tests/EmailComposerTest.php',
            __DIR__.'/packages/mailer/Tests/Twig/TranslationExtensionTest.php',
            __DIR__.'/packages/open-api/Tests/EventListener/RequestQueryParameterFetcherListenerTest.php',
        ],
        Rector\Php83\Rector\ClassMethod\AddOverrideAttributeToOverriddenMethodsRector::class,
        Rector\Php83\Rector\ClassConst\AddTypeToConstRector::class => [
            __DIR__.'/packages/fixer/Tests/fixtures',
        ],
        Rector\Php85\Rector\Property\AddOverrideAttributeToOverriddenPropertiesRector::class,

        // Symfony
        Rector\Symfony\CodeQuality\Rector\Class_\ControllerMethodInjectionToConstructorRector::class,
        // @see `sonata-project/admin-bundle/src/Route/RouteCollection.php:186`
        Rector\Symfony\CodeQuality\Rector\ClassMethod\ActionSuffixRemoverRector::class => [
            __DIR__.'/packages/sonata-integration-bundle/Console/Controller/ExecutionController.php',
            __DIR__.'/packages/sonata-integration-bundle/CronJob/Controller/CronJobController.php',
            __DIR__.'/packages/sonata-integration-bundle/CronJob/Controller/CronJobExecutionController.php',
            __DIR__.'/packages/sonata-integration-bundle/Messenger/Controller/MessageController.php',
            __DIR__.'/packages/sonata-integration-bundle/User/Controller/RefreshUserLockController.php',
            __DIR__.'/packages/sonata-integration-bundle/User/Controller/TwoFactorAuthenticationController.php',
            __DIR__.'/packages/sonata-import-bundle/Controller/ImportController.php',
        ],
        Rector\Symfony\Symfony61\Rector\Class_\MagicClosureTwigExtensionToNativeMethodsRector::class => [
            __DIR__.'/packages/mailer/Twig/TranslationExtension.php',
        ],
    ])
;

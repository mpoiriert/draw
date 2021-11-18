<?php

namespace Draw\Bundle\OpenApiBundle\Tests\DependencyInjection;

use Draw\Bundle\OpenApiBundle\DependencyInjection\Configuration;
use Draw\Component\Tester\DependencyInjection\ConfigurationTestCase;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class ConfigurationTest extends ConfigurationTestCase
{
    public function createConfiguration(): ConfigurationInterface
    {
        return new Configuration();
    }

    public function getDefaultConfiguration(): array
    {
        return [
            'openApi' => [
                'enabled' => true,
                'cleanOnDump' => true,
                'versioning' => [
                    'enabled' => false,
                    'versions' => [
                    ],
                ],
                'definitionAliases' => [
                ],
                'classNamingFilters' => [
                    0 => 'Draw\\Component\\OpenApi\\Naming\\AliasesClassNamingFilter',
                ],
            ],
            'doctrine' => [
                'enabled' => true,
            ],
            'request' => [
                'enabled' => true,
                'queryParameter' => [
                    'enabled' => true,
                ],
                'bodyDeserialization' => [
                    'enabled' => true,
                ],
            ],
            'response' => [
                'enabled' => true,
                'serializeNull' => true,
                'exceptionHandler' => [
                    'enabled' => true,
                    'useDefaultExceptionsStatusCodes' => true,
                    'ignoreConstraintInvalidValue' => false,
                    'exceptionsStatusCodes' => [
                    ],
                    'violationKey' => 'errors',
                ],
            ],
        ];
    }

    public function provideTestInvalidConfiguration(): iterable
    {
        return [];
    }
}

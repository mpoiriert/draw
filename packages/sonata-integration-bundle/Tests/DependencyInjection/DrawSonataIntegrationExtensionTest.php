<?php

namespace Draw\Bundle\SonataIntegrationBundle\Tests\DependencyInjection;

use Draw\Bundle\SonataIntegrationBundle\DependencyInjection\DrawSonataIntegrationExtension;
use Draw\Component\Tester\Test\DependencyInjection\ExtensionTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Component\DependencyInjection\Extension\Extension;

#[CoversClass(DrawSonataIntegrationExtension::class)]
class DrawSonataIntegrationExtensionTest extends ExtensionTestCase
{
    public function createExtension(): Extension
    {
        return new DrawSonataIntegrationExtension();
    }

    public function getConfiguration(): array
    {
        return [
            'configuration' => [
                'enabled' => false,
            ],
            'console' => [
                'enabled' => false,
            ],
            'cron_job' => [
                'enabled' => false,
            ],
            'messenger' => [
                'enabled' => false,
            ],
            'user' => [
                'enabled' => false,
            ],
        ];
    }

    public static function provideTestHasServiceDefinition(): iterable
    {
        yield [null];
    }
}

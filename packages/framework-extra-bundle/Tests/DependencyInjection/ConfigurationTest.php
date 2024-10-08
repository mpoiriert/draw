<?php

namespace Draw\Bundle\FrameworkExtraBundle\Tests\DependencyInjection;

use Draw\Bundle\FrameworkExtraBundle\DependencyInjection\Configuration;
use Draw\Component\Tester\Test\DependencyInjection\ConfigurationTestCase;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * @internal
 */
class ConfigurationTest extends ConfigurationTestCase
{
    public function createConfiguration(): ConfigurationInterface
    {
        return new Configuration();
    }

    public function getDefaultConfiguration(): array
    {
        return [
            'symfony_console_path' => null,
        ];
    }

    public static function provideTestInvalidConfiguration(): iterable
    {
        yield [
            ['invalid' => true],
            'Unrecognized option invalid under draw_framework_extra. Available option is symfony_console_path.',
        ];
    }
}

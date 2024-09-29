<?php

namespace Draw\Bundle\TesterBundle\Tests\DependencyInjection;

use Draw\Bundle\TesterBundle\DependencyInjection\Configuration;
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
        return ['profiling' => ['enabled' => true]];
    }

    public static function provideTestInvalidConfiguration(): iterable
    {
        yield [
            ['invalid' => true],
            'Unrecognized option invalid under draw_tester. Available option is profiling.',
        ];
    }
}

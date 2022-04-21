<?php

namespace Draw\Bundle\FrameworkExtraBundle\Tests\DependencyInjection;

use Draw\Bundle\FrameworkExtraBundle\Bridge\Monolog\Processor\RequestHeadersProcessor;
use Draw\Bundle\FrameworkExtraBundle\Bridge\Monolog\Processor\TokenProcessor;
use Draw\Component\Log\Monolog\Processor\DelayProcessor;

class DrawFrameworkExtraExtensionLogTest extends DrawFrameworkExtraExtensionTest
{
    public function getConfiguration(): array
    {
        $configuration = parent::getConfiguration();

        $configuration['log'] = [
            'enable_all_processors' => true,
        ];

        return $configuration;
    }

    public function provideTestHasServiceDefinition(): iterable
    {
        yield from parent::provideTestHasServiceDefinition();
        yield ['draw.log.console_command_processor'];
        yield ['draw.log.console_command_processor.key_decorator'];
        yield ['draw.log.delay_processor'];
        yield [DelayProcessor::class, 'draw.log.delay_processor'];
        yield ['draw.log.request_headers_processor'];
        yield [RequestHeadersProcessor::class, 'draw.log.request_headers_processor'];
        yield ['draw.log.token_processor'];
        yield [TokenProcessor::class, 'draw.log.token_processor'];
    }
}

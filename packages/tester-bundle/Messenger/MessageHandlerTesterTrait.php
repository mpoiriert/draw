<?php

namespace Draw\Bundle\TesterBundle\Messenger;

use PHPUnit\Framework\TestCase;

trait MessageHandlerTesterTrait
{
    public static function assertMessageHandlerConfiguration(string $expectedFilePath): void
    {
        $handlerDumper = static::getContainer()->get(HandlerConfigurationDumper::class);

        \assert($handlerDumper instanceof HandlerConfigurationDumper);

        $result = $handlerDumper->xmlDump();

        if (!file_exists($expectedFilePath)) {
            file_put_contents($expectedFilePath, $result);

            TestCase::fail('Configuration file created at '.$expectedFilePath.'. Please review it and commit it.');
        }

        TestCase::assertXmlStringEqualsXmlFile(
            $expectedFilePath,
            $result,
        );
    }
}

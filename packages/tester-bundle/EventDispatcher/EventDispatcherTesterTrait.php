<?php

namespace Draw\Bundle\TesterBundle\EventDispatcher;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Tester\CommandTester;

trait EventDispatcherTesterTrait
{
    protected static array $eventDispatcherFileCleaners = [
        [EventDispatcherTesterTrait::class, 'cleanEventDispatcherFileContainerReference'],
    ];

    protected static function cleanEventDispatcherFile(string $filePath): void
    {
        foreach (static::$eventDispatcherFileCleaners as $cleaner) {
            \call_user_func($cleaner, $filePath);
        }
    }

    /**
     * Clean reference in file that looks like this.
     *
     * <callable type="function" name="onKernelControllerArguments" class="ContainerPKL8sSi\RequestPayloadValueResolverGhostB42fd45" priority="0"/>
     *
     * By Removing the unique string pattern in the class attribute using DomDocument.
     *
     * The result after cleaning will be:
     *
     * <callable type="function" name="onKernelControllerArguments" class="Container\RequestPayloadValueResolverGhost" priority="0"/>
     *
     * Clean when the class name contains Container and Ghost in its name.
     */
    public static function cleanEventDispatcherFileContainerReference(string $filePath): void
    {
        $dom = new \DOMDocument();
        $dom->load($filePath);

        $xpath = new \DOMXPath($dom);

        $nodes = $xpath->query('//callable[@class]');

        foreach ($nodes as $node) {
            \assert($node instanceof \DOMElement);
            $class = $node->getAttribute('class');
            if (preg_match('/Container.*Ghost/', $class)) {
                // Use regex to match only the alphanumeric patterns that follow "Container" and "Ghost"
                $class = preg_replace('/(?<=Container)[A-Za-z0-9]+|(?<=Ghost)[A-Za-z0-9]+/', '__cleaned__', $class);
                $node->setAttribute('class', $class);
            }
        }

        $dom->save($filePath);
    }

    public static function assertEventDispatcherConfiguration(string $expectedFilePath, string $dispatcherName = 'event_dispatcher'): void
    {
        $commandTester = new CommandTester(
            static::getContainer()->get('console.command.event_dispatcher_debug')
        );

        $commandTester->execute([
            '--dispatcher' => $dispatcherName,
            '--format' => 'xml',
        ]);

        $display = $commandTester->getDisplay();

        $resultFilePath = tempnam(sys_get_temp_dir(), 'event_dispatcher_configuration');

        file_put_contents($resultFilePath, $display);

        static::cleanEventDispatcherFile($resultFilePath);

        register_shutdown_function('unlink', $resultFilePath);

        if (!file_exists($expectedFilePath)) {
            copy($resultFilePath, $expectedFilePath);

            TestCase::fail($dispatcherName.' configuration file created at '.$expectedFilePath.'. Please review it and commit it.');
        }

        TestCase::assertXmlFileEqualsXmlFile(
            $expectedFilePath,
            $resultFilePath,
        );
    }
}

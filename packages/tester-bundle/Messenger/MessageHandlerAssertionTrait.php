<?php

namespace Draw\Bundle\TesterBundle\Messenger;

use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerInterface;

trait MessageHandlerAssertionTrait
{
    abstract public static function getContainer(): ContainerInterface;

    public function assertHandlerMessageConfiguration(string $handlerClass, array $configuration): void
    {
        $handleMessagesMappingProvider = static::getContainer()->get(HandleMessagesMappingProvider::class);
        $handlerConfiguration = $handleMessagesMappingProvider->getHandlerConfiguration($handlerClass);

        TestCase::assertNotNull(
            $handlerConfiguration,
            \sprintf('"%s" is not registered as a message handler', $handlerClass)
        );

        $busses = $handleMessagesMappingProvider->getBussesNames();

        foreach ($configuration as $index => $messages) {
            if (class_exists($index)) {
                foreach ($busses as $busName) {
                    $configuration[$busName] = array_merge($configuration[$busName] ?? [], [$index => $messages]);
                }
                unset($configuration[$index]);
            }
        }

        foreach ($configuration as $busName => $messages) {
            foreach ($messages as $messageName => $methods) {
                $configuration[$busName][$messageName] = (array) $methods;
                ksort($configuration[$busName]);
                sort($configuration[$busName][$messageName]);
            }
        }
        ksort($configuration);
        TestCase::assertSame($configuration, $handlerConfiguration);
    }
}

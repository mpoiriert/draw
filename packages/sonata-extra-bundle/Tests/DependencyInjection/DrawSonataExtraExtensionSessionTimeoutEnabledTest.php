<?php

namespace Draw\Bundle\SonataExtraBundle\Tests\DependencyInjection;

use Draw\Bundle\SonataExtraBundle\DependencyInjection\DrawSonataExtraExtension;
use Draw\Bundle\SonataExtraBundle\EventListener\SessionTimeoutRequestListener;
use Symfony\Component\DependencyInjection\Extension\Extension;

/**
 * @internal
 */
class DrawSonataExtraExtensionSessionTimeoutEnabledTest extends DrawSonataExtraExtensionTest
{
    public function createExtension(): Extension
    {
        return new DrawSonataExtraExtension();
    }

    public function getConfiguration(): array
    {
        return [
            ...parent::getConfiguration(),
            'session_timeout' => [
                'enabled' => true,
                'delay' => 900,
            ],
        ];
    }

    public static function provideServiceDefinitionCases(): iterable
    {
        yield from parent::provideServiceDefinitionCases();
        yield [SessionTimeoutRequestListener::class];
    }

    public function testSessionTimeoutDefinition(): void
    {
        static::assertSame(
            900,
            $this->getContainerBuilder()
                ->getDefinition(SessionTimeoutRequestListener::class)
                ->getArgument('$delay')
        );
    }
}

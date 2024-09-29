<?php

namespace Draw\Bundle\UserBundle\Tests\DependencyInjection;

use Draw\Bundle\UserBundle\EventListener\TwoFactorAuthenticationEntityListener;
use Draw\Bundle\UserBundle\EventListener\TwoFactorAuthenticationListener;
use Draw\Bundle\UserBundle\Security\TwoFactorAuthentication\Enforcer\IndecisiveTwoFactorAuthenticationEnforcer;
use Draw\Bundle\UserBundle\Security\TwoFactorAuthentication\Enforcer\TwoFactorAuthenticationEnforcerInterface;

/**
 * @internal
 */
class DrawUserExtensionWithEnforce2faTest extends DrawUserExtensionTest
{
    public function getConfiguration(): array
    {
        $configuration = parent::getConfiguration();

        $configuration['enforce_2fa'] = [
            'enabled' => true,
            'enable_route' => 'test-route',
        ];

        return $configuration;
    }

    public static function provideTestHasServiceDefinition(): iterable
    {
        yield from parent::provideTestHasServiceDefinition();
        yield [TwoFactorAuthenticationEntityListener::class];
        yield [TwoFactorAuthenticationListener::class];
        yield [TwoFactorAuthenticationEnforcerInterface::class, IndecisiveTwoFactorAuthenticationEnforcer::class];
        yield [IndecisiveTwoFactorAuthenticationEnforcer::class];
    }

    public function testTwoFactorAuthenticationSubscriber(): void
    {
        static::assertSame(
            'test-route',
            $this->getContainerBuilder()
                ->getDefinition(TwoFactorAuthenticationListener::class)
                ->getArgument('$enableRoute')
        );
    }
}

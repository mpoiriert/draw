<?php

namespace Draw\Bundle\UserBundle\Tests\DependencyInjection;

use Draw\Bundle\UserBundle\Sonata\Extension\TwoFactorAuthenticationExtension;

class DrawUserExtensionWith2faEnabledTest extends DrawUserExtensionTest
{
    public function getConfiguration(): array
    {
        return ['sonata' => ['2fa_enabled' => true]];
    }

    public function provideTestHasServiceDefinition(): iterable
    {
        yield from parent::provideTestHasServiceDefinition();
        yield [TwoFactorAuthenticationExtension::class];
    }
}

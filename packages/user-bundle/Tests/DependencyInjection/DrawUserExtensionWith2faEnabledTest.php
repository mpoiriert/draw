<?php

namespace Draw\Bundle\UserBundle\Tests\DependencyInjection;

use Draw\Bundle\UserBundle\Sonata\Controller\TwoFactorAuthenticationController;
use Draw\Bundle\UserBundle\Sonata\Extension\TwoFactorAuthenticationExtension;
use Scheb\TwoFactorBundle\SchebTwoFactorBundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class DrawUserExtensionWith2faEnabledTest extends DrawUserExtensionTest
{
    public function getConfiguration(): array
    {
        return ['sonata' => ['2fa' => ['enabled' => true]]];
    }

    public function provideTestHasServiceDefinition(): iterable
    {
        yield from parent::provideTestHasServiceDefinition();
        yield [TwoFactorAuthenticationExtension::class];
        yield [TwoFactorAuthenticationController::class];
    }

    protected function preLoad(array $config, ContainerBuilder $containerBuilder): void
    {
        $containerBuilder->setParameter(
            'kernel.bundles',
            [
                'SchebTwoFactorBundle' => SchebTwoFactorBundle::class,
            ]
        );
    }
}
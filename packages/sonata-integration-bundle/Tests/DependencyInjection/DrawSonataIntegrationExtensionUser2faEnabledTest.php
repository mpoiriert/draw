<?php

namespace Draw\Bundle\SonataIntegrationBundle\Tests\DependencyInjection;

use Draw\Bundle\SonataIntegrationBundle\User\Controller\TwoFactorAuthenticationController;
use Draw\Bundle\SonataIntegrationBundle\User\Extension\TwoFactorAuthenticationExtension;
use Draw\Bundle\UserBundle\Tests\Fixtures\Entity\User;
use Scheb\TwoFactorBundle\SchebTwoFactorBundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * @covers \Draw\Bundle\SonataIntegrationBundle\DependencyInjection\DrawSonataIntegrationExtension
 */
class DrawSonataIntegrationExtensionUser2faEnabledTest extends DrawSonataIntegrationExtensionUserEnabledTest
{
    public function getConfiguration(): array
    {
        $configuration = parent::getConfiguration();

        $configuration['user']['2fa'] = [
            'enabled' => true,
        ];

        return $configuration;
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

        $containerBuilder->setParameter('draw_user.user_entity_class', User::class);
    }
}

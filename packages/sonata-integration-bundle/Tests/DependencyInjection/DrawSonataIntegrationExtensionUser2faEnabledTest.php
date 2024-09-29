<?php

namespace Draw\Bundle\SonataIntegrationBundle\Tests\DependencyInjection;

use Draw\Bundle\SonataIntegrationBundle\DependencyInjection\DrawSonataIntegrationExtension;
use Draw\Bundle\SonataIntegrationBundle\User\Controller\TwoFactorAuthenticationController;
use Draw\Bundle\SonataIntegrationBundle\User\Extension\TwoFactorAuthenticationExtension;
use Draw\Bundle\UserBundle\Tests\Fixtures\Entity\User;
use PHPUnit\Framework\Attributes\CoversClass;
use Scheb\TwoFactorBundle\SchebTwoFactorBundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * @internal
 */
#[CoversClass(DrawSonataIntegrationExtension::class)]
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

    public static function provideTestHasServiceDefinition(): iterable
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

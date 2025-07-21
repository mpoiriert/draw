<?php

namespace Draw\Bundle\SonataIntegrationBundle\Tests\DependencyInjection;

use Draw\Bundle\SonataIntegrationBundle\DependencyInjection\DrawSonataIntegrationExtension;
use Draw\Bundle\SonataIntegrationBundle\User\Admin\Extension\PasswordChangeEnforcerExtension;
use Draw\Bundle\SonataIntegrationBundle\User\Block\UserCountBlock;
use Draw\Bundle\SonataIntegrationBundle\User\Controller\LoginController;
use Draw\Bundle\SonataIntegrationBundle\User\Twig\UserAdminExtension;
use Draw\Bundle\SonataIntegrationBundle\User\Twig\UserAdminRuntime;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * @internal
 */
#[CoversClass(DrawSonataIntegrationExtension::class)]
class DrawSonataIntegrationExtensionUserEnabledTest extends DrawSonataIntegrationExtensionTest
{
    public function getConfiguration(): array
    {
        $configuration = parent::getConfiguration();

        $configuration['user'] = [
            'enabled' => true,
            'user_lock' => [
                'enabled' => false,
            ],
        ];

        return $configuration;
    }

    public static function provideServiceDefinitionCases(): iterable
    {
        yield [LoginController::class];
        yield [UserCountBlock::class];
        yield [UserAdminExtension::class];
        yield [UserAdminRuntime::class];
        yield [PasswordChangeEnforcerExtension::class];
        yield ['draw.sonata.user.action.request_password_change_action'];
    }
}

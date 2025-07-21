<?php

namespace Draw\Bundle\SonataIntegrationBundle\Tests\DependencyInjection;

use App\Sonata\Admin\UserAdmin;
use Draw\Bundle\SonataIntegrationBundle\DependencyInjection\DrawSonataIntegrationExtension;
use Draw\Bundle\SonataIntegrationBundle\User\Admin\Extension\RefreshUserLockExtension;
use Draw\Bundle\SonataIntegrationBundle\User\Admin\Extension\UnlockUserLockExtension;
use Draw\Bundle\SonataIntegrationBundle\User\Admin\UserLockAdmin;
use Draw\Bundle\SonataIntegrationBundle\User\Controller\RefreshUserLockController;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * @internal
 */
#[CoversClass(DrawSonataIntegrationExtension::class)]
class DrawSonataIntegrationExtensionUserLockEnabledTest extends DrawSonataIntegrationExtensionUserEnabledTest
{
    public function getConfiguration(): array
    {
        $configuration = parent::getConfiguration();

        $configuration['user']['user_lock'] = [
            'enabled' => true,
        ];

        return $configuration;
    }

    public static function provideServiceDefinitionCases(): iterable
    {
        yield from parent::provideServiceDefinitionCases();
        yield [UserLockAdmin::class];
        yield [RefreshUserLockController::class];
        yield ['draw.sonata.user.action.unlock_user_action'];
        yield [UnlockUserLockExtension::class];
        yield [RefreshUserLockExtension::class];
    }

    public function testUserAdminExtensionConfiguration(): void
    {
        static::assertSame(
            UserAdmin::class,
            $this->getContainerBuilder()
                ->getDefinition(UnlockUserLockExtension::class)
                ->getTag('sonata.admin.extension')[0]['target']
        );

        static::assertSame(
            UserAdmin::class,
            $this->getContainerBuilder()
                ->getDefinition(RefreshUserLockExtension::class)
                ->getTag('sonata.admin.extension')[0]['target']
        );
    }
}

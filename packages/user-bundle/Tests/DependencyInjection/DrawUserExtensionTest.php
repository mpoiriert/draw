<?php

namespace Draw\Bundle\UserBundle\Tests\DependencyInjection;

use Draw\Bundle\UserBundle\Controller\Api\ConnectionTokensController;
use Draw\Bundle\UserBundle\DependencyInjection\DrawUserExtension;
use Draw\Bundle\UserBundle\Entity\UserLock;
use Draw\Bundle\UserBundle\EventListener\EncryptPasswordUserEntityListener;
use Draw\Bundle\UserBundle\EventListener\UserRequestInterceptedListener;
use Draw\Bundle\UserBundle\EventListener\UserRequestInterceptorListener;
use Draw\Bundle\UserBundle\Feed\FlashUserFeed;
use Draw\Bundle\UserBundle\Feed\UserFeedInterface;
use Draw\Bundle\UserBundle\MessageHandler\PreventNotHandleMessageHandler;
use Draw\Bundle\UserBundle\Security\TwoFactorAuthentication\QrCodeGenerator;
use Draw\Bundle\UserBundle\Tests\Fixtures\Entity\User;
use Draw\Component\Tester\Test\DependencyInjection\ExtensionTestCase;
use Symfony\Component\DependencyInjection\Extension\Extension;

/**
 * @internal
 */
class DrawUserExtensionTest extends ExtensionTestCase
{
    public function createExtension(): Extension
    {
        return new DrawUserExtension();
    }

    public function getConfiguration(): array
    {
        return [
            'user_entity_class' => User::class,
            'email_writers' => [
                'enabled' => false,
            ],
            'account_locker' => [
                'enabled' => false,
            ],
            'enforce_2fa' => [
                'enabled' => false,
            ],
            'password_change_enforcer' => [
                'enabled' => false,
            ],
        ];
    }

    public static function provideTestHasServiceDefinition(): iterable
    {
        yield [ConnectionTokensController::class];
        yield [UserRequestInterceptedListener::class];
        yield [FlashUserFeed::class];
        yield [EncryptPasswordUserEntityListener::class];
        yield [UserRequestInterceptorListener::class];
        yield ['draw_user.user_repository'];
        yield [UserFeedInterface::class, FlashUserFeed::class];
        yield ['Doctrine\ORM\EntityRepository $drawUserEntityRepository', 'draw_user.user_repository'];
        yield [PreventNotHandleMessageHandler::class];
        yield [QrCodeGenerator::class];
    }

    public function testExcludePathsParameter(): void
    {
        static::assertSame(
            [
                (new \ReflectionClass(UserLock::class))->getFileName(),
            ],
            $this
                ->getContainerBuilder()
                ->getParameter('draw.user.orm.metadata_driver.exclude_paths')
        );
    }
}

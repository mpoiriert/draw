<?php

namespace Draw\Bundle\UserBundle\Tests\DependencyInjection;

use Draw\Bundle\UserBundle\Controller\Api\ConnectionTokensController;
use Draw\Bundle\UserBundle\DependencyInjection\DrawUserExtension;
use Draw\Bundle\UserBundle\EventListener\UserRequestInterceptedSubscriber;
use Draw\Bundle\UserBundle\Feed\SessionUserFeed;
use Draw\Bundle\UserBundle\Feed\UserFeedInterface;
use Draw\Bundle\UserBundle\Listener\EncryptPasswordUserEntityListener;
use Draw\Bundle\UserBundle\Listener\UserRequestInterceptorSubscriber;
use Draw\Bundle\UserBundle\MessageHandler\PreventNotHandleMessageHandler;
use Draw\Bundle\UserBundle\Sonata\Block\UserCountBlock;
use Draw\Bundle\UserBundle\Sonata\Controller\LoginController;
use Draw\Bundle\UserBundle\Sonata\Form\AdminLoginForm;
use Draw\Bundle\UserBundle\Sonata\Form\ChangePasswordForm;
use Draw\Bundle\UserBundle\Sonata\Form\Enable2fa;
use Draw\Bundle\UserBundle\Sonata\Form\Enable2faForm;
use Draw\Bundle\UserBundle\Sonata\Form\ForgotPasswordForm;
use Draw\Bundle\UserBundle\Sonata\Twig\UserAdminExtension;
use Draw\Bundle\UserBundle\Sonata\Twig\UserAdminRuntime;
use Draw\Bundle\UserBundle\Tests\Fixtures\Entity\User;
use Draw\Component\Mailer\DrawPostOfficeBundle;
use Draw\Component\Tester\DependencyInjection\ExtensionTestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;

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
        ];
    }

    public function provideTestHasServiceDefinition(): iterable
    {
        yield [ConnectionTokensController::class];
        yield [UserRequestInterceptedSubscriber::class];
        yield [SessionUserFeed::class];
        yield [EncryptPasswordUserEntityListener::class];
        yield [UserRequestInterceptorSubscriber::class];
        yield [UserCountBlock::class];
        yield [LoginController::class];
        yield [AdminLoginForm::class];
        yield [ChangePasswordForm::class];
        yield [Enable2fa::class];
        yield [Enable2faForm::class];
        yield [ForgotPasswordForm::class];
        yield [UserAdminExtension::class];
        yield [UserAdminRuntime::class];
        yield ['draw_user.user_repository'];
        yield [UserFeedInterface::class, SessionUserFeed::class];
        yield ['Doctrine\ORM\EntityRepository $drawUserEntityRepository', 'draw_user.user_repository'];
        yield [PreventNotHandleMessageHandler::class];
    }

    protected function preLoad(array $config, ContainerBuilder $containerBuilder): void
    {
        $containerBuilder->setParameter(
            'kernel.bundles',
            [
                'DrawPostOfficeBundle' => DrawPostOfficeBundle::class,
            ]
        );
    }
}

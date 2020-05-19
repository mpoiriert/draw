<?php namespace Draw\Bundle\UserBundle\Tests\DependencyInjection;

use Draw\Bundle\UserBundle\Controller\Api\ConnectionTokensController;
use Draw\Bundle\UserBundle\DependencyInjection\DrawUserExtension;
use Draw\Bundle\UserBundle\EmailWriter\ForgotPasswordEmailWriter;
use Draw\Bundle\UserBundle\Feed\SessionUserFeed;
use Draw\Bundle\UserBundle\Feed\UserFeedInterface;
use Draw\Bundle\UserBundle\Jwt\JwtAuthenticator;
use Draw\Bundle\UserBundle\Listener\EncryptPasswordUserEntityListener;
use Draw\Bundle\UserBundle\MessageHandler\AutoConnectMessageHandler;
use Draw\Bundle\UserBundle\Security\MessageAuthenticator;
use Draw\Bundle\UserBundle\Sonata\Block\UserCountBlock;
use Draw\Bundle\UserBundle\Sonata\Controller\LoginController;
use Draw\Bundle\UserBundle\Sonata\Form\AdminLoginForm;
use Draw\Bundle\UserBundle\Sonata\Form\ChangePasswordForm;
use Draw\Bundle\UserBundle\Sonata\Form\ForgotPasswordForm;
use Draw\Bundle\UserBundle\Sonata\Security\AdminLoginAuthenticator;
use Draw\Bundle\UserBundle\Sonata\Twig\UserAdminExtension;
use Draw\Bundle\UserBundle\Sonata\Twig\UserAdminRuntime;
use Draw\Component\Tester\DependencyInjection\ExtensionTestCase;
use Symfony\Component\DependencyInjection\Extension\Extension;

class DrawUserExtensionTest extends ExtensionTestCase
{
    public function createExtension(): Extension
    {
        return new DrawUserExtension();
    }

    public function getConfiguration(): array
    {
        return [];
    }

    public function provideTestHasServiceDefinition(): iterable
    {
        yield [ConnectionTokensController::class];
        yield [ForgotPasswordEmailWriter::class];
        yield [SessionUserFeed::class];
        yield [JwtAuthenticator::class];
        yield [EncryptPasswordUserEntityListener::class];
        yield [AutoConnectMessageHandler::class];
        yield [MessageAuthenticator::class];
        yield [UserCountBlock::class];
        yield [LoginController::class];
        yield [AdminLoginForm::class];
        yield [ChangePasswordForm::class];
        yield [ForgotPasswordForm::class];
        yield [AdminLoginAuthenticator::class];
        yield [UserAdminExtension::class];
        yield [UserAdminRuntime::class];
        yield ['draw_user.user_repository'];
        yield [UserFeedInterface::class, SessionUserFeed::class];
    }
}
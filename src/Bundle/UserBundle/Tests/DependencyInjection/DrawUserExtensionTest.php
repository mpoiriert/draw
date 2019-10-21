<?php namespace Draw\Bundle\UserBundle\Tests\DependencyInjection;

use Draw\Bundle\UserBundle\DependencyInjection\DrawUserExtension;
use Draw\Bundle\UserBundle\EmailWriter\ForgotPasswordEmailWriter;
use Draw\Bundle\UserBundle\Listener\EncryptPasswordUserEntityListener;
use Draw\Bundle\UserBundle\Sonata\Block\UserCountBlock;
use Draw\Bundle\UserBundle\Sonata\Controller\LoginController;
use Draw\Bundle\UserBundle\Sonata\Form\AdminLoginForm;
use Draw\Bundle\UserBundle\Sonata\Security\AdminLoginAuthenticator;
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
        yield [ForgotPasswordEmailWriter::class];
        yield [EncryptPasswordUserEntityListener::class];
        yield [UserCountBlock::class];
        yield [LoginController::class];
        yield [AdminLoginForm::class];
        yield [AdminLoginAuthenticator::class];
    }
}
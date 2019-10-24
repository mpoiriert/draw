<?php namespace Draw\Bundle\UserBundle\Tests\DependencyInjection;

use Draw\Bundle\UserBundle\DependencyInjection\DrawUserExtension;
use Draw\Bundle\UserBundle\Sonata\Block\UserCountBlock;
use Draw\Bundle\UserBundle\Sonata\Controller\LoginController;
use Draw\Bundle\UserBundle\Sonata\Form\AdminLoginForm;
use Draw\Bundle\UserBundle\Sonata\Form\ChangePasswordForm;
use Draw\Bundle\UserBundle\Sonata\Security\AdminLoginAuthenticator;
use Draw\Bundle\UserBundle\Sonata\Twig\UserAdminExtension;
use Draw\Bundle\UserBundle\Sonata\Twig\UserAdminRuntime;
use Symfony\Component\DependencyInjection\Extension\Extension;

class DrawUserExtensionWithoutSonataTest extends DrawUserExtensionTest
{
    public function createExtension(): Extension
    {
        return new DrawUserExtension();
    }

    public function getConfiguration(): array
    {
        return ['sonata' => false];
    }

    public function provideTestHasServiceDefinition(): iterable
    {
        yield from $this->removeProvidedService(
            [
                UserCountBlock::class,
                LoginController::class,
                AdminLoginForm::class,
                ChangePasswordForm::class,
                AdminLoginAuthenticator::class,
                UserAdminRuntime::class,
                UserAdminExtension::class
            ],
            parent::provideTestHasServiceDefinition()
        );
    }
}
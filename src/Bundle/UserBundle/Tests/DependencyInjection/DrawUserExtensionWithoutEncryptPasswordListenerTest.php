<?php namespace Draw\Bundle\UserBundle\Tests\DependencyInjection;

use Draw\Bundle\UserBundle\DependencyInjection\DrawUserExtension;
use Draw\Bundle\UserBundle\Listener\EncryptPasswordUserEntityListener;
use Symfony\Component\DependencyInjection\Extension\Extension;

class DrawUserExtensionWithoutEncryptPasswordListenerTest extends DrawUserExtensionTest
{
    public function createExtension(): Extension
    {
        return new DrawUserExtension();
    }

    public function getConfiguration(): array
    {
        return ['encrypt_password_listener' => false];
    }

    public function provideTestHasServiceDefinition(): iterable
    {
        yield from $this->removeProvidedService(
            [
                EncryptPasswordUserEntityListener::class
            ],
            parent::provideTestHasServiceDefinition()
        );
    }
}
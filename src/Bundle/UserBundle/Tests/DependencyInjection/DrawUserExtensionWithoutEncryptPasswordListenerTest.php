<?php namespace Draw\Bundle\UserBundle\Tests\DependencyInjection;

use Draw\Bundle\UserBundle\Listener\EncryptPasswordUserEntityListener;

class DrawUserExtensionWithoutEncryptPasswordListenerTest extends DrawUserExtensionTest
{
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
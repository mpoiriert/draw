<?php namespace Draw\Bundle\CommandBundle\Tests\DependencyInjection;

use Draw\Bundle\CommandBundle\Authentication\Listener\CommandLineAuthenticatorListener;
use Draw\Bundle\CommandBundle\Authentication\SystemAuthenticator;
use Draw\Bundle\CommandBundle\Authentication\SystemAuthenticatorInterface;

class DrawCommandExtensionWithAuthenticationTest extends DrawCommandExtensionTest
{
    public function getConfiguration(): array
    {
        return ['authentication' => null];
    }

    public function provideTestHasServiceDefinition(): iterable
    {
        yield from parent::provideTestHasServiceDefinition();
        yield [CommandLineAuthenticatorListener::class];
        yield [SystemAuthenticator::class];
        yield [SystemAuthenticatorInterface::class, SystemAuthenticator::class];
    }
}
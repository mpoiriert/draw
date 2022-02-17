<?php

namespace Draw\Bundle\UserBundle\Tests\DependencyInjection;

use Draw\Bundle\UserBundle\DependencyInjection\DrawUserExtension;
use Draw\Bundle\UserBundle\PasswordRecovery\EmailWriter\ForgotPasswordEmailWriter;
use Draw\Bundle\UserBundle\Tests\Fixtures\Entity\User;
use Symfony\Component\DependencyInjection\Extension\Extension;

class DrawUserExtensionWithPasswordRecoveryEnabledTest extends DrawUserExtensionTest
{
    public function createExtension(): Extension
    {
        return new DrawUserExtension();
    }

    public function getConfiguration(): array
    {
        return [
            'user_entity_class' => User::class,
            'password_recovery' => ['enabled' => true],
        ];
    }

    public function provideTestHasServiceDefinition(): iterable
    {
        yield from parent::provideTestHasServiceDefinition();
        yield [ForgotPasswordEmailWriter::class];
    }
}

<?php

namespace Draw\Bundle\UserBundle\Tests\DependencyInjection;

use Draw\Bundle\UserBundle\Jwt\JwtAuthenticator;

class DrawUserExtensionWithJwtAuthenticatorTest extends DrawUserExtensionTest
{
    public function getConfiguration(): array
    {
        return [
            'jwt_authenticator' => [
                'key' => 'unique-key',
            ],
        ];
    }

    public function provideTestHasServiceDefinition(): iterable
    {
        yield from parent::provideTestHasServiceDefinition();
        yield [JwtAuthenticator::class];
    }
}

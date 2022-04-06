<?php

namespace Draw\Bundle\FrameworkExtraBundle\Tests\DependencyInjection;

use Draw\Component\Security\Jwt\JwtEncoder;

class DrawFrameworkExtraExtensionJwtEncoderTest extends DrawFrameworkExtraExtensionTest
{
    public function getConfiguration(): array
    {
        return [
            'jwt_encoder' => [
                'key' => 'unique-key',
            ],
        ];
    }

    public function provideTestHasServiceDefinition(): iterable
    {
        yield from parent::provideTestHasServiceDefinition();
        yield ['draw.jwt_encoder'];
        yield [JwtEncoder::class, 'draw.jwt_encoder'];
    }
}

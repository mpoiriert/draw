<?php

namespace Draw\Bundle\FrameworkExtraBundle\Tests\DependencyInjection;

use Draw\Component\Security\Jwt\JwtEncoder;

class DrawFrameworkExtraExtensionJwtEncoderTest extends DrawFrameworkExtraExtensionTest
{
    public function getConfiguration(): array
    {
        $configuration = parent::getConfiguration();

        $configuration['jwt_encoder'] = [
            'key' => 'unique-key',
        ];

        return $configuration;
    }

    public function provideTestHasServiceDefinition(): iterable
    {
        yield from parent::provideTestHasServiceDefinition();
        yield ['draw.jwt_encoder'];
        yield [JwtEncoder::class, 'draw.jwt_encoder'];
    }
}

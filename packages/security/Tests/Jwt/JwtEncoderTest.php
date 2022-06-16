<?php

namespace Draw\Component\Security\Tests\Jwt;

use DateTimeImmutable;
use Draw\Component\Security\Jwt\JwtEncoder;
use Firebase\JWT\ExpiredException;
use Firebase\JWT\SignatureInvalidException;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Draw\Component\Security\Jwt\JwtEncoder
 */
class JwtEncoderTest extends TestCase
{
    private JwtEncoder $service;

    protected function setUp(): void
    {
        $this->service = new JwtEncoder(
            uniqid('key-'),
            'HS256'
        );
    }

    public function testEncode(): void
    {
        $token = $this->service->encode(['value' => $value = uniqid('value-')]);

        static::assertSame(
            $value,
            $this->service->decode($token)->value
        );
    }

    public function testDecodeExpired(): void
    {
        $token = $this->service->encode(
            ['value' => uniqid('value-')],
            new DateTimeImmutable('- 10 minutes')
        );

        $this->expectException(ExpiredException::class);

        $this->service->decode($token);
    }

    public function testDecodeWrongKey(): void
    {
        $service = new JwtEncoder(uniqid('key-'), 'HS256');

        $token = $this->service->encode(
            ['value' => uniqid('value-')],
            new DateTimeImmutable('- 10 minutes')
        );

        $this->expectException(SignatureInvalidException::class);

        $service->decode($token);
    }
}

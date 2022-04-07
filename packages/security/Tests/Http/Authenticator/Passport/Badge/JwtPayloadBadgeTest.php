<?php

namespace Draw\Component\Security\Tests\Http\Authenticator\Passport\Badge;

use Draw\Component\Security\Http\Authenticator\Passport\Badge\JwtPayloadBadge;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Draw\Component\Security\Http\Authenticator\Passport\Badge\JwtPayloadBadge
 */
class JwtPayloadBadgeTest extends TestCase
{
    private JwtPayloadBadge $entity;

    private array $payload;

    public function setUp(): void
    {
        $this->entity = new JwtPayloadBadge(
            $this->payload = [
                uniqid('key-') => uniqid('value-'),
                uniqid('key-') => uniqid('value-'),
            ]
        );
    }

    public function testGetPayloadKeyValueArray(): void
    {
        $this->entity = new JwtPayloadBadge(
            [
                $key = uniqid('key-') => $value = (object) [
                    uniqid('key-') => uniqid('value-'),
                ],
            ]
        );

        $this->assertSame(
            (array) $value,
            $this->entity->getPayloadKeyValue($key)
        );
    }

    public function testGetPayloadKeyValue(): void
    {
        $resultPayload = [];
        foreach (array_keys($this->payload) as $key) {
            $resultPayload[$key] = $this->entity->getPayloadKeyValue($key);
        }

        $this->assertSame(
            $this->payload,
            $resultPayload
        );
    }

    public function testIsResolved(): void
    {
        $this->assertFalse($this->entity->isResolved());
    }

    public function testMarkPayloadKeyResolvedNotEmpty(): void
    {
        $this->entity->markPayloadKeyResolved(array_keys($this->payload)[0]);

        $this->assertFalse($this->entity->isResolved());
    }

    public function testMarkPayloadKeyResolved(): void
    {
        foreach (array_keys($this->payload) as $key) {
            $this->entity->markPayloadKeyResolved($key);
        }

        $this->assertTrue($this->entity->isResolved());
    }

    public function testCreateIfNeeded(): void
    {
        $this->assertNull(JwtPayloadBadge::createIfNeeded([]));

        $this->assertInstanceOf(
            JwtPayloadBadge::class,
            $badge = JwtPayloadBadge::createIfNeeded(
                $payload = [
                    'exp' => time(),
                    'iat' => time(),
                    'nbf' => time(),
                    $extraIgnoredKey = uniqid('key-') => uniqid(),
                    $extraKey = uniqid('key-') => uniqid(),
                ],
                [$extraIgnoredKey]
            )
        );

        foreach ($payload as $key => $value) {
            switch ($key) {
                case $extraKey:
                    $this->assertSame($value, $badge->getPayloadKeyValue($key));
                    break;
                default:
                    $this->assertNull($badge->getPayloadKeyValue($key));
                    break;
            }
        }
    }
}

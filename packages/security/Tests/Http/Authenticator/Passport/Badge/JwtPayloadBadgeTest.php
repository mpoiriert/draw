<?php

namespace Draw\Component\Security\Tests\Http\Authenticator\Passport\Badge;

use Draw\Component\Security\Http\Authenticator\Passport\Badge\JwtPayloadBadge;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(JwtPayloadBadge::class)]
class JwtPayloadBadgeTest extends TestCase
{
    private JwtPayloadBadge $entity;

    private array $payload;

    protected function setUp(): void
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

        static::assertSame(
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

        static::assertSame(
            $this->payload,
            $resultPayload
        );
    }

    public function testIsResolved(): void
    {
        static::assertFalse($this->entity->isResolved());
    }

    public function testMarkPayloadKeyResolvedNotEmpty(): void
    {
        $this->entity->markPayloadKeyResolved(array_keys($this->payload)[0]);

        static::assertFalse($this->entity->isResolved());
    }

    public function testMarkPayloadKeyResolved(): void
    {
        foreach (array_keys($this->payload) as $key) {
            $this->entity->markPayloadKeyResolved($key);
        }

        static::assertTrue($this->entity->isResolved());
    }

    public function testCreateIfNeeded(): void
    {
        static::assertNull(JwtPayloadBadge::createIfNeeded([]));

        static::assertInstanceOf(
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
            match ($key) {
                $extraKey => static::assertSame($value, $badge->getPayloadKeyValue($key)),
                default => static::assertNull($badge->getPayloadKeyValue($key)),
            };
        }
    }
}

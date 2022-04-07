<?php

namespace Draw\Component\Security\Http\Authenticator\Passport\Badge;

use Symfony\Component\Security\Http\Authenticator\Passport\Badge\BadgeInterface;

class JwtPayloadBadge implements BadgeInterface
{
    private const KEYS_TO_IGNORE = [
        'exp',
        'nbf',
        'iat',
    ];

    private array $payload;

    public function __construct(array $payload)
    {
        // Enforce assoc array instead of object recursively
        $this->payload = json_decode(json_encode($payload), true);
    }

    public function getPayloadKeyValue(string $key)
    {
        return $this->payload[$key] ?? null;
    }

    public function markPayloadKeyResolved(string $key): void
    {
        unset($this->payload[$key]);
    }

    public function isResolved(): bool
    {
        return empty($this->payload);
    }

    public static function createIfNeeded(array $payload, array $extraKeyToIgnores = []): ?self
    {
        $resultPayload = array_diff_key(
            $payload,
            array_flip(array_merge(static::KEYS_TO_IGNORE, $extraKeyToIgnores))
        );

        return $resultPayload ? new static($resultPayload) : null;
    }
}

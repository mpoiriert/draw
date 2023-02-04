<?php

namespace Draw\Component\Security\Http\Authenticator\Passport\Badge;

use Symfony\Component\Security\Http\Authenticator\Passport\Badge\BadgeInterface;

final class JwtPayloadBadge implements BadgeInterface
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
        $this->payload = json_decode(json_encode($payload, \JSON_THROW_ON_ERROR), true, 512, \JSON_THROW_ON_ERROR);
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
            array_flip(array_merge(self::KEYS_TO_IGNORE, $extraKeyToIgnores))
        );

        return $resultPayload ? new self($resultPayload) : null;
    }
}

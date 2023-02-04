<?php

namespace Draw\Component\Security\Jwt;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class JwtEncoder
{
    public function __construct(private string $key, private string $algorithm)
    {
    }

    public function encode(array $payload, ?\DateTimeInterface $expiration = null): string
    {
        if ($expiration) {
            $payload['exp'] = $expiration->getTimestamp();
        }

        return JWT::encode(
            $payload,
            $this->key,
            $this->algorithm
        );
    }

    public function decode(string $token): object
    {
        return JWT::decode($token, new Key($this->key, $this->algorithm));
    }
}

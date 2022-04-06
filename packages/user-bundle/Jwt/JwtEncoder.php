<?php

namespace Draw\Bundle\UserBundle\Jwt;

use DateTimeInterface;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class JwtEncoder
{
    private string $algorithm;

    private string $key;

    public function __construct(string $key, string $algorithm)
    {
        $this->algorithm = $algorithm;
        $this->key = $key;
    }

    public function encode(array $payload, ?DateTimeInterface $expiration = null): string
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

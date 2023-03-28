<?php

namespace Draw\Component\Security\Jwt;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class JwtEncoder
{
    public function __construct(private string $key, private string $algorithm, private ?string $privateKey = null, private ?string $passphrase = null)
    {
    }

    public function encode(array $payload, ?\DateTimeInterface $expiration = null): string
    {
        if ($expiration) {
            $payload['exp'] = $expiration->getTimestamp();
        }

        $key = $this->key;

        if ($this->privateKey) {
            $key = openssl_pkey_get_private($this->privateKey, $this->passphrase);
        }

        return JWT::encode(
            $payload,
            $key,
            $this->algorithm
        );
    }

    public function decode(string $token): object
    {
        return JWT::decode($token, new Key($this->key, $this->algorithm));
    }
}

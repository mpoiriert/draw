<?php

namespace App\Tests\Controller\Api;

use App\Tests\TestCase;
use DateTime;
use Firebase\JWT\JWT;

class ConnectionTokensControllerTest extends TestCase
{
    public function testRefresh()
    {
        $token = JWT::encode(
            [
                'userId' => 'invalid',
                'exp' => (new DateTime('+ 7 days'))->getTimestamp(),
            ],
            'acme',
            'HS256'
        );

        $this->httpTester()
            ->post(
                '/api/connection-tokens/refresh',
                '',
                ['Authorization' => 'Bearer '.$token]
            )
            ->assertStatus(403);
    }
}

<?php namespace App\Tests;

class CorsTest extends TestCase
{
    public function testCors()
    {
        $this->httpTester()
            ->options(
                '/api',
                [
                    'origin' => 'http://localhost',
                    'access-control-request-method' => 'GET'
                ]
            )
            ->assertStatus(204);
    }
}
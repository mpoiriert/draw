<?php namespace App\Tests\Controller\Api;

use App\Tests\TestCase;

class ConnectionTokensControllerTest extends TestCase
{
    public function testOptionsCreate()
    {
        $this->httpTester()
            ->options('/api/connection-tokens')
            ->assertStatus(200);
    }
}
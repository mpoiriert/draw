<?php namespace App\Tests\Controller;

use App\Tests\TestCase;
use Draw\Component\Profiling\Sql\SqlAssertionBuilder;

class PingControllerTest extends TestCase
{
    public function testPing()
    {
        $response = $this->httpTester()
            ->get('/ping')
            ->assertStatus(200)
            ->getResponseBodyContents();

        $this->assertSame('pong', $response);

        $this->assertMetrics(SqlAssertionBuilder::create(1));
    }
}
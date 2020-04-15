<?php namespace App\Tests\Controller\Api;

use App\Tests\TestCase;
use Draw\Component\Tester\Data\AgainstJsonFileTester;

class DashboardControllerTest extends TestCase
{
    public function testGetAction()
    {
        $this->httpTester()
            ->get('/api/dashboard')
            ->assertStatus(200)
            ->toJsonDataTester()
            ->test(
                new AgainstJsonFileTester(
                    __DIR__ . '/fixtures/DashboardControllerTest_testGetAction.json'
                )
            );
    }
}
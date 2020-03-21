<?php namespace App\Tests\Controller\Api;

use App\Tests\TestCase;
use Draw\Component\Tester\Data\AgainstJsonFileTester;

class UsersControllerTest extends TestCase
{
    public function testUsersAction_options()
    {
        $this->httpTester()
            ->options('/api/users')
            ->assertStatus(200)
            ->toJsonDataTester()
            ->test(
                new AgainstJsonFileTester(
                    __DIR__ . '/fixtures/UsersControllerTest_testUsersAction_options.json'
                )
            );
    }

    public function testUsersAction()
    {
        return $this->httpTester()
            ->get('/api/users')
            ->assertStatus(200)
            ->toJsonDataTester()
            ->getData();

    }

    /**
     * @depends testUsersAction
     */
    public function testOptionsDeleteUser($pagers)
    {
        $this->httpTester()
            ->options('/api/users/' . $pagers->data[0]->id)
            ->assertStatus(200);
    }

    public function testOptionsCreateUser()
    {
        $this->httpTester()
            ->options('/api/users')
            ->assertStatus(200);
    }
}
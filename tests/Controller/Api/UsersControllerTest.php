<?php namespace App\Tests\Controller\Api;

use App\Entity\User;
use App\Tests\TestCase;
use Doctrine\ORM\EntityManagerInterface;
use Draw\Component\Tester\Data\AgainstJsonFileTester;

class UsersControllerTest extends TestCase
{
    /**
     * @beforeClass
     * @afterClass
     */
    static public function cleanUp()
    {
        static::getService(EntityManagerInterface::class)
            ->createQueryBuilder()
            ->delete(User::class, 'user')
            ->andWhere('user.email = :email')
            ->setParameter('email', 'test@example.com')
            ->getQuery()
            ->execute();
    }

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

    public function testOptionsCreateUser_connected()
    {
        $this->connect();
        $this->httpTester()
            ->options('/api/users')
            ->assertStatus(200)
            ->toJsonDataTester()
            ->test(
                new AgainstJsonFileTester(
                    __DIR__ . '/fixtures/UsersControllerTest_testUsersAction_options_connected.json'
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

    public function testUsersCreateAction()
    {
        $this->connect();
        return $this->httpTester()
            ->post(
                '/api/users',
                json_encode([
                    'email' => 'test@example.com',
                    'plainPassword' => 'test',
                    'tags' => [
                        ['id' => 1]
                    ]
                ])
            )
            ->assertStatus(200)
            ->toJsonDataTester()
            ->getData();
    }

    /**
     * @depends testUsersCreateAction
     *
     * @param $user
     * @return mixed
     */
    public function testUsersEditAction($user)
    {
        $this->httpTester()
            ->put(
                '/api/users/' . $user->id,
                json_encode([
                    'tags' => []
                ])
            )
            ->assertStatus(200)
            ->toJsonDataTester()
            ->path('tags')->assertSame([]);
    }


    /**
     * @depends testUsersCreateAction
     *
     * @param $user
     */
    public function testUsersDeleteAction($user)
    {
        $this->httpTester()
            ->delete('/api/users/' . $user->id)
            ->assertStatus(204);
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
}
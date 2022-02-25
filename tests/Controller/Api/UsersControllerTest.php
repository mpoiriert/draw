<?php

namespace App\Tests\Controller\Api;

use App\Entity\User;
use App\Tests\TestCase;
use Doctrine\ORM\EntityManagerInterface;

class UsersControllerTest extends TestCase
{
    /**
     * @beforeClass
     * @afterClass
     */
    public static function cleanUp()
    {
        static::getService(EntityManagerInterface::class)
            ->createQueryBuilder()
            ->delete(User::class, 'user')
            ->andWhere('user.email = :email')
            ->setParameter('email', 'test@example.com')
            ->getQuery()
            ->execute();
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
                        ['id' => 1],
                    ],
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
     *
     * @return mixed
     */
    public function testUsersEditAction($user)
    {
        $this->httpTester()
            ->put(
                '/api/users/'.$user->id,
                json_encode([
                    'tags' => [],
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
            ->delete('/api/users/'.$user->id)
            ->assertStatus(204);
    }
}

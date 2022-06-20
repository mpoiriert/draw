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
    public static function cleanUp(): void
    {
        static::getService(EntityManagerInterface::class)
            ->createQueryBuilder()
            ->delete(User::class, 'user')
            ->andWhere('user.email = :email')
            ->setParameter('email', 'test@example.com')
            ->getQuery()
            ->execute();
    }

    public function testUsersAction(): void
    {
        $this->httpTester()
            ->get('/api/users')
            ->assertStatus(200);
    }

    public function testUsersCreateAction(): object
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
     */
    public function testUsersEditAction(object $user): void
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
     */
    public function testUsersDeleteAction(object $user): void
    {
        $this->httpTester()
            ->delete('/api/users/'.$user->id)
            ->assertStatus(204);
    }
}

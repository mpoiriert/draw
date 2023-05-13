<?php

namespace App\Tests\Controller\Api;

use App\Entity\User;
use App\Tests\TestCase;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LogLevel;
use Symfony\Component\HttpFoundation\Response;

class UsersControllerTest extends TestCase
{
    /**
     * @beforeClass
     *
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
        $data = $this->connect($this->httpTester())
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

        $handler = static::getContainer()->get('monolog.handler.testing');

        static::assertTrue(
            $handler->hasRecord('[UsersController] Create new user', LogLevel::INFO)
        );

        return $data;
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
    public function testSetTagsAction(object $user): void
    {
        $this->httpTester()
            ->put(
                '/api/users/'.$user->id.'/tags',
                json_encode([
                    ['id' => 1],
                ])
            )
            ->assertStatus(200)
            ->toJsonDataTester()
            ->assertCount(1)
            ->path('[0].id')
            ->assertSame(1);
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

    public function testCreateUnsupportedContentType(): void
    {
        $this->httpTester()
            ->post(
                '/api/users',
                '<test />',
                ['Content-Type' => 'application/xml']
            )
            ->assertStatus(Response::HTTP_UNSUPPORTED_MEDIA_TYPE);
    }
}

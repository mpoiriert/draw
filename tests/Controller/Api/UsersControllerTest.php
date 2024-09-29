<?php

namespace App\Tests\Controller\Api;

use App\Entity\User;
use App\Test\PHPUnit\Extension\SetUpAutowire\AutowireAdminUser;
use Doctrine\ORM\EntityManagerInterface;
use Draw\Bundle\TesterBundle\Mailer\TemplatedMailerAssertionsTrait;
use Draw\Bundle\TesterBundle\PHPUnit\Extension\SetUpAutowire\AutowireClient;
use Draw\Bundle\TesterBundle\PHPUnit\Extension\SetUpAutowire\AutowireLoggerTester;
use Draw\Bundle\TesterBundle\WebTestCase;
use Draw\Bundle\UserBundle\Email\ForgotPasswordEmail;
use Draw\Component\Tester\PHPUnit\Extension\SetUpAutowire\AutowiredInterface;
use Monolog\Handler\TestHandler;
use Monolog\Level;
use PHPUnit\Framework\Attributes\AfterClass;
use PHPUnit\Framework\Attributes\BeforeClass;
use PHPUnit\Framework\Attributes\Depends;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Component\HttpFoundation\Response;

/**
 * @internal
 */
class UsersControllerTest extends WebTestCase implements AutowiredInterface
{
    use TemplatedMailerAssertionsTrait;

    #[AutowireClient]
    private KernelBrowser $client;

    #[AutowireLoggerTester]
    private TestHandler $loggerTester;

    #[AutowireAdminUser]
    private User $user;

    #[
        BeforeClass,
        AfterClass,
    ]
    public static function cleanUp(): void
    {
        static::getContainer()
            ->get(EntityManagerInterface::class)
            ->createQueryBuilder()
            ->delete(User::class, 'user')
            ->andWhere('user.email = :email')
            ->setParameter('email', 'test@example.com')
            ->getQuery()
            ->execute()
        ;
    }

    public function testUsersAction(): void
    {
        $this->client
            ->request('GET', '/api/users')
        ;

        static::assertResponseIsSuccessful();
    }

    public function testUsersCreateAction(): object
    {
        $this->client->loginUser($this->user);

        $this->client
            ->jsonRequest(
                'POST',
                '/api/users',
                [
                    'email' => 'test@example.com',
                    'plainPassword' => 'test',
                    'tags' => [
                        ['id' => 1],
                    ],
                ]
            )
        ;

        static::assertResponseIsSuccessful();

        static::assertTrue(
            $this->loggerTester->hasRecord('[UsersController] Create new user', Level::Info)
        );

        return static::getJsonResponseDataTester()->getData();
    }

    #[Depends('testUsersCreateAction')]
    public function testUsersEditAction(object $user): void
    {
        $this->client->loginUser($this->user);

        $this->client
            ->jsonRequest(
                'PUT',
                '/api/users/'.$user->id,
                [
                    'tags' => [],
                ]
            )
        ;

        static::assertResponseIsSuccessful();

        static::getJsonResponseDataTester()
            ->path('tags')
            ->assertSame([])
        ;
    }

    #[Depends('testUsersCreateAction')]
    public function testSetTagsAction(object $user): void
    {
        $this->client->loginUser($this->user);

        $this->client
            ->jsonRequest(
                'PUT',
                '/api/users/'.$user->id.'/tags',
                [
                    ['id' => 1],
                ]
            )
        ;

        static::assertResponseIsSuccessful();

        static::getJsonResponseDataTester()
            ->path('[0].id')
            ->assertSame(1)
        ;
    }

    #[Depends('testUsersCreateAction')]
    public function testSendResetPasswordEmail(object $user): void
    {
        $this->client
            ->loginUser($this->user)
            ->jsonRequest(
                'POST',
                '/api/users/'.$user->id.'/reset-password-email',
            )
        ;

        static::assertResponseIsSuccessful();

        static::assertEmailCount(1);

        static::assertHtmlTemplatedEmailCount(1, '@DrawUser/Email/reset_password_email.html.twig');
        static::assertTextTemplatedEmailCount(0, 'toto');

        static::assertInstanceOf(
            ForgotPasswordEmail::class,
            static::getHtmlTemplatedMailerEvent()->getMessage()
        );

        static::assertNull(
            static::getTextTemplatedMailerEvent()
        );
    }

    #[Depends('testUsersCreateAction')]
    public function testUsersDeleteAction(object $user): void
    {
        $this->client->loginUser($this->user);

        $this->client
            ->jsonRequest('DELETE', '/api/users/'.$user->id)
        ;

        static::assertResponseStatusCodeSame(Response::HTTP_NO_CONTENT);
    }

    public function testCreateUnsupportedContentType(): void
    {
        $this->client->loginUser($this->user);

        $this->client
            ->request(
                'POST',
                '/api/users',
                server: ['CONTENT_TYPE' => 'application/xml'],
                content: '<test />'
            )
        ;

        static::assertResponseStatusCodeSame(Response::HTTP_UNSUPPORTED_MEDIA_TYPE);
    }
}

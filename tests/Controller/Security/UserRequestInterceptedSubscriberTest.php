<?php

namespace App\Tests\Controller\Security;

use App\Entity\User;
use Draw\Bundle\TesterBundle\PHPUnit\Extension\SetUpAutowire\AutowireClient;
use Draw\Bundle\TesterBundle\PHPUnit\Extension\SetUpAutowire\AutowireEntity;
use Draw\Bundle\TesterBundle\WebTestCase;
use Draw\Component\Tester\PHPUnit\Extension\SetUpAutowire\AutowiredInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Component\HttpFoundation\Response;

/**
 * @internal
 */
class UserRequestInterceptedSubscriberTest extends WebTestCase implements AutowiredInterface
{
    #[AutowireClient(
        server: [
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
        ]
    )]
    private KernelBrowser $client;

    #[AutowireEntity(['email' => '2fa-admin@example.com'])]
    private User $user;

    public function testRedirectedErrorMessage(): void
    {
        $this->client->loginUser($this->user);

        $this->client->request('get', '/api/users');

        static::assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);

        static::getJsonResponseDataTester()
            ->path('message')
            ->assertSame('User request intercepted: 2fa_need_enabling')
        ;
    }
}

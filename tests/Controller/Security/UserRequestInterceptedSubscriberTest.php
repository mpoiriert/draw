<?php

namespace App\Tests\Controller\Security;

use App\Tests\AuthenticatorTestTrait;
use Draw\Bundle\TesterBundle\PHPUnit\Extension\SetUpAutowire\AutowireClient;
use Draw\Bundle\TesterBundle\PHPUnit\Extension\SetUpAutowire\AutowiredInterface;
use Draw\Bundle\TesterBundle\WebTestCase;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Component\HttpFoundation\Response;

class UserRequestInterceptedSubscriberTest extends WebTestCase implements AutowiredInterface
{
    use AuthenticatorTestTrait;

    #[AutowireClient(
        server: [
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
        ]
    )]
    private KernelBrowser $client;

    public function testRedirectedErrorMessage(): void
    {
        static::setAuthorizationHeader($this->client, '2fa-admin@example.com');

        $this->client->request('get', '/api/users');

        static::assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);

        $dataTester = static::getJsonResponseDataTester();

        static::assertSame(
            'User request intercepted: 2fa_need_enabling',
            $dataTester->getData('message')
        );

        $dataTester
            ->path('message')
            ->assertSame('User request intercepted: 2fa_need_enabling');
    }
}

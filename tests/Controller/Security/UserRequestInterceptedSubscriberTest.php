<?php

namespace App\Tests\Controller\Security;

use App\Tests\AuthenticatorTestTrait;
use Draw\Bundle\TesterBundle\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class UserRequestInterceptedSubscriberTest extends WebTestCase
{
    use AuthenticatorTestTrait;

    public function testRedirectedErrorMessage(): void
    {
        $client = static::createJsonClient();

        static::setAuthorizationHeader($client, '2fa-admin@example.com');

        $client->request('get', '/api/users');

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

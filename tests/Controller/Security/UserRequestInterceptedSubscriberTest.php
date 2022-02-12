<?php

namespace App\Tests\Controller\Security;

use App\Tests\TestCase;
use Symfony\Component\HttpFoundation\Response;

class UserRequestInterceptedSubscriberTest extends TestCase
{
    public function testRedirectedErrorMessage(): void
    {
        $this->connect('2fa-admin@example.com');

        $this->httpTester()
            ->get('/api/users')
            ->assertStatus(Response::HTTP_FORBIDDEN)
            ->toJsonDataTester()
            ->path('message')
            ->assertSame('User request intercepted: 2fa_need_enabling');
    }
}

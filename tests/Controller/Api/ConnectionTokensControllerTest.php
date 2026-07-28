<?php

namespace App\Tests\Controller\Api;

use Draw\Bundle\TesterBundle\PHPUnit\Extension\SetUpAutowire\AutowireClient;
use Draw\Bundle\TesterBundle\WebTestCase;
use Draw\Component\Tester\PHPUnit\Extension\SetUpAutowire\AutowiredInterface;
use Firebase\JWT\JWT;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Component\HttpFoundation\Response;

/**
 * @internal
 */
class ConnectionTokensControllerTest extends WebTestCase implements AutowiredInterface
{
    #[AutowireClient]
    private KernelBrowser $client;

    public function testRefresh(): void
    {
        $token = JWT::encode(
            [
                'userId' => 'invalid',
                'exp' => (new \DateTime('+ 7 days'))->getTimestamp(),
            ],
            'acme',
            'HS256'
        );

        $this->client
            ->jsonRequest(
                'POST',
                '/api/connection-tokens/refresh',
                [],
                ['Authorization' => 'Bearer '.$token]
            )
        ;

        static::assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
    }
}

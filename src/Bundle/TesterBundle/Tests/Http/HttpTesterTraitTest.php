<?php

namespace Draw\Bundle\TesterBundle\Tests\Http;

use Draw\Bundle\TesterBundle\Tests\TestCase;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class HttpTesterTraitTest extends TestCase
{
    public function testGetClientService(): void
    {
        $this
            ->httpTester()
            ->get('/test')
            ->assertStatus(200);

        $this->assertInstanceOf(
            UrlGeneratorInterface::class,
            $this->getClientService(UrlGeneratorInterface::class)
        );
    }
}
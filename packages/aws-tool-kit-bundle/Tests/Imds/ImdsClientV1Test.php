<?php

namespace Draw\Bundle\AwsToolKitBundle\Tests\Imds;

use Draw\Bundle\AwsToolKitBundle\Imds\ImdsClientV1;
use PHPUnit\Framework\TestCase;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

/**
 * @covers \Draw\Bundle\AwsToolKitBundle\Imds\ImdsClientV1
 */
class ImdsClientV1Test extends TestCase
{
    private ImdsClientV1 $imdsClientV1;

    private HttpClientInterface $httpClient;

    public function setUp(): void
    {
        $this->imdsClientV1 = new ImdsClientV1(
            $this->httpClient = $this->createMock(HttpClientInterface::class)
        );
    }

    public function testGetCurrentInstanceId(): void
    {
        $this->httpClient
            ->expects($this->once())
            ->method('request')
            ->with(
                'GET',
                'http://169.254.169.254/latest/meta-data/instance-id'
            )
            ->willReturn($response = $this->createMock(ResponseInterface::class));

        $response
            ->expects($this->once())
            ->method('getContent')
            ->with()
            ->willReturn($instanceId = uniqid('instance-id-'));

        $this->assertSame(
            $instanceId,
            $this->imdsClientV1->getCurrentInstanceId()
        );
    }
}

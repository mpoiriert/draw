<?php

namespace Draw\Component\AwsToolKit\Tests\Imds;

use Draw\Component\AwsToolKit\Imds\ImdsClientV2;
use PHPUnit\Framework\TestCase;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

/**
 * @covers \Draw\Component\AwsToolKit\Imds\ImdsClientV2
 */
class ImdsClientV2Test extends TestCase
{
    private ImdsClientV2 $imdsClientV1;

    private HttpClientInterface $httpClient;

    public function setUp(): void
    {
        $this->imdsClientV1 = new ImdsClientV2(
            $this->httpClient = $this->createMock(HttpClientInterface::class)
        );
    }

    public function testGetCurrentInstanceId(): void
    {
        $tokenResponse = $this->createMock(ResponseInterface::class);
        $tokenResponse
            ->expects($this->once())
            ->method('getContent')
            ->with()
            ->willReturn($token = uniqid('token-'));

        $instanceIdResponse = $this->createMock(ResponseInterface::class);
        $instanceIdResponse
            ->expects($this->once())
            ->method('getContent')
            ->with()
            ->willReturn($instanceId = uniqid('instance-id-'));

        $this->httpClient
            ->expects($this->exactly(2))
            ->method('request')
            ->withConsecutive(
                [
                    'PUT',
                    'http://169.254.169.254/latest/api/token',
                    [
                        'headers' => [
                            'X-aws-ec2-metadata-token-ttl-seconds' => 3600,
                        ],
                    ],
                ],
                [
                    'GET',
                    'http://169.254.169.254/latest/meta-data/instance-id',
                    [
                        'headers' => [
                            'X-aws-ec2-metadata-token' => $token,
                        ],
                    ],
                ]
            )
            ->willReturnOnConsecutiveCalls(
                $tokenResponse,
                $instanceIdResponse,
            );

        $this->assertSame(
            $instanceId,
            $this->imdsClientV1->getCurrentInstanceId()
        );
    }
}

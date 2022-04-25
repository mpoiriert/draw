<?php

namespace Draw\Component\AwsToolKit\Imds;

use Symfony\Contracts\HttpClient\HttpClientInterface;

class ImdsClientV2 implements ImdsClientInterface
{
    private ?string $token = null;

    private HttpClientInterface $httpClient;

    public function __construct(HttpClientInterface $httpClient)
    {
        $this->httpClient = $httpClient;
    }

    public function getCurrentInstanceId(): ?string
    {
        return $this->httpClient->request(
            'GET',
            'http://169.254.169.254/latest/meta-data/instance-id',
            [
                'headers' => [
                    'X-aws-ec2-metadata-token' => $this->getToken(),
                ],
            ]
        )->getContent();
    }

    private function getToken(): string
    {
        if (null === $this->token) {
            $this->token =
                $this->httpClient->request(
                    'PUT',
                    'http://169.254.169.254/latest/api/token',
                    [
                        'headers' => [
                            'X-aws-ec2-metadata-token-ttl-seconds' => 3600,
                        ],
                    ]
                )->getContent();
        }

        return $this->token;
    }
}

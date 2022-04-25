<?php

namespace Draw\Component\AwsToolKit\Imds;

use Symfony\Contracts\HttpClient\HttpClientInterface;

class ImdsClientV1 implements ImdsClientInterface
{
    private HttpClientInterface $httpClient;

    public function __construct(HttpClientInterface $httpClient)
    {
        $this->httpClient = $httpClient;
    }

    public function getCurrentInstanceId(): ?string
    {
        return $this->httpClient->request(
            'GET',
            'http://169.254.169.254/latest/meta-data/instance-id'
        )->getContent();
    }
}

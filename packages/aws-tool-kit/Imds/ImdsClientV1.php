<?php

namespace Draw\Component\AwsToolKit\Imds;

use Symfony\Contracts\HttpClient\HttpClientInterface;

class ImdsClientV1 implements ImdsClientInterface
{
    public function __construct(private HttpClientInterface $httpClient)
    {
    }

    public function getCurrentInstanceId(): ?string
    {
        return $this->httpClient->request(
            'GET',
            'http://169.254.169.254/latest/meta-data/instance-id'
        )->getContent();
    }
}

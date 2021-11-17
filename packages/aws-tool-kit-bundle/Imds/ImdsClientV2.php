<?php

namespace Draw\Bundle\AwsToolKitBundle\Imds;

class ImdsClientV2 implements ImdsClientInterface
{
    /**
     * @var string|null
     */
    private $token = null;

    public function getCurrentInstanceId(): ?string
    {
        return file_get_contents(
            'http://169.254.169.254/latest/meta-data/instance-id',
            false,
            stream_context_create(
                [
                    'http' => [
                        'method' => 'GET',
                        'header' => 'X-aws-ec2-metadata-token: '.$this->getToken(),
                    ],
                ]
            )
        );
    }

    private function getToken(): string
    {
        if (null === $this->token) {
            $this->token = file_get_contents(
                'http://169.254.169.254/latest/api/token',
                false,
                stream_context_create(
                    [
                        'http' => [
                            'method' => 'PUT',
                            'header' => 'X-aws-ec2-metadata-token-ttl-seconds: 3600',
                        ],
                    ]
                )
            );
        }

        return $this->token;
    }
}

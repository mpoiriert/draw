<?php

namespace Draw\Bundle\AwsToolKitBundle\Imds;

class ImdsClientV1 implements ImdsClientInterface
{
    public function getCurrentInstanceId(): ?string
    {
        return file_get_contents('http://169.254.169.254/latest/meta-data/instance-id');
    }
}

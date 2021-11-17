<?php

namespace Draw\Bundle\AwsToolKitBundle\Imds;

interface ImdsClientInterface
{
    public function getCurrentInstanceId(): ?string;
}

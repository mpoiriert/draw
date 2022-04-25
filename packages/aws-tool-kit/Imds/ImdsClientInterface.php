<?php

namespace Draw\Component\AwsToolKit\Imds;

interface ImdsClientInterface
{
    public function getCurrentInstanceId(): ?string;
}

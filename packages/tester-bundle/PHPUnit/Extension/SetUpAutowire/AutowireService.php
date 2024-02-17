<?php

namespace Draw\Bundle\TesterBundle\PHPUnit\Extension\SetUpAutowire;

#[\Attribute(\Attribute::TARGET_PROPERTY)]
class AutowireService
{
    public function __construct(private ?string $serviceId = null)
    {
    }

    public function getServiceId(): ?string
    {
        return $this->serviceId;
    }
}

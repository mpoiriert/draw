<?php

namespace Draw\Bundle\DashboardBundle\Annotations;

use Draw\Component\OpenApi\Schema\VendorInterface;

interface VendorPropertyInterface extends VendorInterface
{
    public function getId(): ?string;

    public function setId(?string $id);
}

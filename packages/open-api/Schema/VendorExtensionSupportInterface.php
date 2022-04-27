<?php

namespace Draw\Component\OpenApi\Schema;

interface VendorExtensionSupportInterface
{
    public function getVendorData();

    public function setVendorData(array $data = null): void;

    public function setVendorDataKey($name, $data): void;
}

<?php

namespace Draw\Component\OpenApi\Schema;

trait VendorExtensionSupportTrait
{
    /**
     * @JMS\Type("array<string,Draw\Component\OpenApi\Schema\MixedData>")
     * @JMS\Accessor(getter="getFakeVendor")
     */
    public ?array $vendor = [];

    public function getFakeVendor()
    {
        return null;
    }

    public function getVendorData(): ?array
    {
        return $this->vendor;
    }

    public function setVendorData(?array $data = null): void
    {
        $this->vendor = $data;
    }

    public function setVendorDataKey($key, $data): void
    {
        $this->vendor[$key] = $data;
    }
}

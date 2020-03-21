<?php namespace Draw\Component\OpenApi\Schema;

trait VendorExtensionSupportTrait
{
    /**
     * @var mixed
     * @JMS\Type("array<string,Draw\Component\OpenApi\Schema\Mixed>")
     * @JMS\Accessor(getter="getFakeVendor")
     */
    public $vendor = [];

    public function getFakeVendor()
    {
        return null;
    }

    public function getVendorData()
    {
        return $this->vendor;
    }

    public function setVendorData(array $data = null)
    {
        return $this->vendor = $data;
    }


    public function setVendorDataKey($key, $data)
    {
        $this->vendor[$key] = $data;
    }
}
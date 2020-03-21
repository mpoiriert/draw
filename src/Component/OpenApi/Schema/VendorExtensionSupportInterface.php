<?php

namespace Draw\Component\OpenApi\Schema;

/**
 * @author Martin Poirier Theoret <mpoiriert@gmail.com>
 */
interface VendorExtensionSupportInterface
{
    public function getVendorData();

    public function setVendorData(array $data = null);

    public function setVendorDataKey($name, $data);
} 
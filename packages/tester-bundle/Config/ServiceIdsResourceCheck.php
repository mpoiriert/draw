<?php

namespace Draw\Bundle\TesterBundle\Config;

use Draw\Bundle\TesterBundle\DrawTesterBundle;
use Symfony\Component\Config\Resource\SelfCheckingResourceInterface;

class ServiceIdsResourceCheck implements SelfCheckingResourceInterface
{
    private $filePath;

    public function __construct($filePath)
    {
        $this->filePath = $filePath;
    }

    public function isFresh($timestamp)
    {
        if (!is_file($this->filePath) || !is_readable($this->filePath)) {
            $ids = [];
        } else {
            $ids = json_decode(file_get_contents($this->filePath));
        }

        if (!count($missingIds = array_diff(array_unique(DrawTesterBundle::$ids), $ids))) {
            return true;
        }

        file_put_contents(
            $this->filePath,
            json_encode(array_merge($ids, $missingIds), JSON_PRETTY_PRINT)
        );

        return false;
    }

    public function __toString()
    {
        return $this->filePath;
    }
}

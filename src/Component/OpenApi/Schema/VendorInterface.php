<?php namespace Draw\Component\OpenApi\Schema;

interface VendorInterface
{
    public function getVendorName(): string;

    public function allowClassLevelConfiguration(): bool;
}
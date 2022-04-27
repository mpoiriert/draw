<?php

namespace Draw\Component\OpenApi\Naming;

interface ClassNamingFilterInterface
{
    public function filterClassName(string $originalClassName, array $context = [], string $newName = null): string;
}

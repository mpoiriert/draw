<?php

namespace Draw\Component\OpenApi\Naming;

interface ClassNamingFilterInterface
{
    /**
     * @param string $originalClassName,
     * @param array $context
     * @param string|null $newName
     * @return string
     */
    public function filterClassName(string $originalClassName, array $context = [], string $newName = null);
}
<?php

namespace Draw\Component\OpenApi\Naming;

interface ClassNamingFilterInterface
{
    /**
     * @param string $originalClassName,
     *
     * @return string
     */
    public function filterClassName(string $originalClassName, array $context = [], string $newName = null);
}

<?php

namespace Draw\Bundle\OpenApiBundle\Versioning;

use Symfony\Component\Routing\Route;

interface VersionMatcherInterface
{
    public function matchVersion(string $version, Route $route): bool;
}

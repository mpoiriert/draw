<?php

namespace Draw\Component\OpenApi\Versioning;

use Symfony\Component\Routing\Route;

interface RouteVersionMatcherInterface
{
    public function matchVersion(string $version, Route $route): bool;
}

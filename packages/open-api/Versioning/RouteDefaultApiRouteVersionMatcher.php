<?php

namespace Draw\Component\OpenApi\Versioning;

use Symfony\Component\Routing\Route;

class RouteDefaultApiRouteVersionMatcher implements RouteVersionMatcherInterface
{
    public function matchVersion(string $version, Route $route): bool
    {
        return $version === (string) $route->getDefault('_api_version');
    }
}

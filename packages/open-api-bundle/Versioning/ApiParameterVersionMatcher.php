<?php

namespace Draw\Bundle\OpenApiBundle\Versioning;

use Symfony\Component\Routing\Route;

class ApiParameterVersionMatcher implements VersionMatcherInterface
{
    public function matchVersion(string $version, Route $route): bool
    {
        return $version === (string) $route->getDefault('_api_version');
    }
}

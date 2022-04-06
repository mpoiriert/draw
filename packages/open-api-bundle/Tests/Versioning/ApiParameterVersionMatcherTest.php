<?php

namespace Draw\Bundle\OpenApiBundle\Tests\Versioning;

use Draw\Bundle\OpenApiBundle\Versioning\ApiParameterVersionMatcher;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Routing\Route;

class ApiParameterVersionMatcherTest extends TestCase
{
    public function testMatchVersionTrue(): void
    {
        $matcher = new ApiParameterVersionMatcher();

        $route = new Route('/test');
        $route->setDefault('_api_version', 'v1');
        $this->assertTrue($matcher->matchVersion('v1', $route));
    }

    public function testMatchVersionFalse(): void
    {
        $matcher = new ApiParameterVersionMatcher();

        $route = new Route('/test');
        $this->assertFalse($matcher->matchVersion('v1', $route));
    }
}

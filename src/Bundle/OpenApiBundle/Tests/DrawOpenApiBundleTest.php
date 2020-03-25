<?php namespace Draw\Bundle\OpenApiBundle\Tests;

use Draw\Component\OpenApi\OpenApi;

class DrawOpenApiBundleTest extends TestCase
{
    public function testGetService()
    {
        $openApi = $this->getService(OpenApi::class);

        $this->assertInstanceOf(OpenApi::class, $openApi);

        return $openApi;
    }
}
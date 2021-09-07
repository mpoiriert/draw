<?php

namespace Draw\Bundle\OpenApiBundle\Tests\Request;

use Draw\Bundle\OpenApiBundle\Tests\TestCase;

/**
 * This is a integration test but mainly to test the RequestBodyParamConverter.
 * It base itself on the configuration of the AppKernel and the Mock TestController.
 */
class RequestBodyParamConverterTest extends TestCase
{
    public function testApply()
    {
        $this->httpTester()
            ->post(
                '/tests',
                json_encode([
                    'property_from_body' => 'propertyValue',
                ])
            )
            ->assertStatus(201)
            ->toJsonDataTester()
            ->path('property_from_body')
            ->assertSame('propertyValue');
    }

    public function testApplyUnsupportedMediaType()
    {
        $this->httpTester()
            ->post(
                '/tests',
                '<test />',
                ['Content-Type' => 'application/xml']
            )
            ->assertStatus(415);
    }
}

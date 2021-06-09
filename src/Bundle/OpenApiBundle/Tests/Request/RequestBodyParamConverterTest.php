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

    public function testApply_failValidation()
    {
        $this->httpTester()
            ->post(
                '/tests',
                json_encode([
                    'property_from_body' => 'invalidValue',
                ])
            )
            ->assertStatus(400)
            ->toJsonDataTester()
            ->path('errors')
            ->assertCount(1)
            ->path('[0]')
            ->assertEquals((object) [
                'propertyPath' => 'propertyFromBody',
                'message' => 'This value should not be equal to "invalidValue".',
                'invalidValue' => 'invalidValue',
                'code' => 'aa2e33da-25c8-4d76-8c6c-812f02ea89dd',
            ]);
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

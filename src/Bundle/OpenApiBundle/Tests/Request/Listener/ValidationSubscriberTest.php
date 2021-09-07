<?php

namespace Draw\Bundle\OpenApiBundle\Tests\Request\Listener;

use Draw\Bundle\OpenApiBundle\Tests\TestCase;

class ValidationSubscriberTest extends TestCase
{
    public function testOnKernelController_body()
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
                'propertyPath' => '$.body.propertyFromBody',
                'message' => 'This value should not be equal to "invalidValue".',
                'invalidValue' => 'invalidValue',
                'code' => 'aa2e33da-25c8-4d76-8c6c-812f02ea89dd',
            ]);
    }

    public function testOnKernelController_queryParameter()
    {
        $this->httpTester()
            ->post(
                '/tests-array',
                ''
            )
            ->assertStatus(400)
            ->toJsonDataTester()
            ->path('errors')
            ->assertCount(1)
            ->path('[0]')
            ->assertEquals((object) [
                'propertyPath' => '$.query.param1',
                'message' => 'This value should not be null.',
                'invalidValue' => null,
                'code' => 'ad32d13f-c3d4-423b-909a-857b961eb720',
            ]);
    }
}
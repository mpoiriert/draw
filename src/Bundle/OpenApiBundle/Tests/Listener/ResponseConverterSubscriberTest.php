<?php namespace Draw\Bundle\OpenApiBundle\Tests\Listener;

use Draw\Bundle\OpenApiBundle\Tests\Mock\Controller\TestController;
use Draw\Bundle\OpenApiBundle\Tests\TestCase;

/**
 * This is a integration test but mainly to test the ResponseConverterSubscriberTest.
 * It base itself on the configuration of the AppKernel and the Mock TestController
 */
class ResponseConverterSubscriberTest extends TestCase
{
    /**
     * @see TestController::createAction()
     */
    public function testOnKernelView()
    {
        $this->httpTester()
            ->post('/tests', '')
            ->assertStatus(201);
    }
}
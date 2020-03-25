<?php namespace Draw\Bundle\OpenApiBundle\Tests\Listener;

use Draw\Bundle\OpenApiBundle\Tests\Mock\Controller\TestController;
use Draw\Bundle\OpenApiBundle\Tests\TestCase;

/**
 * This is a integration test but mainly to test the QueryParameterFetcherSubscriber.
 * It base itself on the configuration of the AppKernel and the Mock TestController
 */
class QueryParameterFetcherSubscriberTest extends TestCase
{
    /**
     * @see TestController::createAction()
     */
    public function testOnKernelController_withValue()
    {
        $this->httpTester()
            ->post('/tests?param1=toto', '')
            ->assertStatus(201)
            ->toJsonDataTester()
            ->path('property')
            ->assertSame('toto');
    }

    /**
     * @see TestController::createAction()
     */
    public function testOnKernelController_defaultValue()
    {
        $this->httpTester()
            ->post('/tests', '')
            ->assertStatus(201)
            ->toJsonDataTester()
            ->path('property')
            ->assertSame('default');
    }
}
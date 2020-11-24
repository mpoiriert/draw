<?php

namespace Draw\Bundle\OpenApiBundle\Tests\Listener;

use Doctrine\Common\Annotations\Reader;
use Draw\Bundle\OpenApiBundle\Request\Listener\QueryParameterFetcherSubscriber;
use Draw\Bundle\OpenApiBundle\Tests\Mock\Controller\TestController;
use Draw\Bundle\OpenApiBundle\Tests\TestCase;
use Draw\Component\OpenApi\Schema\QueryParameter;
use Prophecy\Argument;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\ControllerEvent;

/**
 * This is a integration test but mainly to test the QueryParameterFetcherSubscriber.
 * It base itself on the configuration of the AppKernel and the Mock TestController.
 */
class QueryParameterFetcherSubscriberTest extends TestCase
{
    /**
     * @var QueryParameterFetcherSubscriber
     */
    private $queryParameterFetcherSubscriber;

    private $reader;

    public function setUp(): void
    {
        $this->reader = $this->prophesize(Reader::class);
        $this->queryParameterFetcherSubscriber = new QueryParameterFetcherSubscriber($this->reader->reveal());
    }

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

    public function provideOnKernelController()
    {
        yield 'boolean-0-false' => ['boolean', '0', false];
        yield 'boolean-1-true' => ['boolean', '1', true];
        yield 'boolean-true-true' => ['boolean', 'true', true];
        yield 'boolean-false-false' => ['boolean', 'false', false];
    }

    /**
     * @dataProvider provideOnKernelController
     */
    public function testOnKernelController(string $type, string $value, $expectedValue)
    {
        $queryParameter = new QueryParameter();
        $queryParameter->name = 'test';
        $queryParameter->type = $type;

        $this->reader->getMethodAnnotations(Argument::any())->shouldBeCalledOnce()->willReturn([$queryParameter]);

        $controller = $this->prophesize(ControllerEvent::class);
        $controller->getRequest()->willReturn($request = new Request());

        $request->query->set('test', $value);

        //This need to exist but will not be used
        $controller->getController()->willReturn([$this, 'testOnKernelController']);

        $this->queryParameterFetcherSubscriber->onKernelController(
            $controller->reveal()
        );

        $this->assertSame(
            $expectedValue,
            $request->attributes->get('test')
        );
    }
}

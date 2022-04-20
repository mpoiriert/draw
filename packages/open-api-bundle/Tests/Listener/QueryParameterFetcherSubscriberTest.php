<?php

namespace Draw\Bundle\OpenApiBundle\Tests\Listener;

use Doctrine\Common\Annotations\Reader;
use Draw\Bundle\OpenApiBundle\Request\Listener\QueryParameterFetcherSubscriber;
use Draw\Bundle\OpenApiBundle\Tests\Mock\Controller\TestController;
use Draw\Bundle\OpenApiBundle\Tests\TestCase;
use Draw\Component\OpenApi\Schema\QueryParameter;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\KernelInterface;

/**
 * This is a integration test but mainly to test the QueryParameterFetcherSubscriber.
 * It base itself on the configuration of the AppKernel and the Mock TestController.
 */
class QueryParameterFetcherSubscriberTest extends TestCase
{
    private QueryParameterFetcherSubscriber $service;

    private Reader $reader;

    public function setUp(): void
    {
        $this->service = new QueryParameterFetcherSubscriber(
            $this->reader = $this->createMock(Reader::class)
        );
    }

    /**
     * @see TestController::createAction()
     */
    public function testOnKernelControllerWithValue(): void
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
    public function testOnKernelControllerDefaultValue(): void
    {
        $this->httpTester()
            ->post('/tests', '')
            ->assertStatus(201)
            ->toJsonDataTester()
            ->path('property')
            ->assertSame('default');
    }

    /**
     * @see TestController::createAction()
     */
    public function testOnKernelControllerArray(): void
    {
        $this->httpTester()
            ->post('/tests-array?param1=toto,tata', '')
            ->assertStatus(200)
            ->toJsonDataTester()
            ->assertSame(['toto', 'tata']);
    }

    public function provideOnKernelController(): iterable
    {
        yield 'boolean-0-false' => ['boolean', '0', false];
        yield 'boolean-1-true' => ['boolean', '1', true];
        yield 'boolean-true-true' => ['boolean', 'true', true];
        yield 'boolean-false-false' => ['boolean', 'false', false];
    }

    /**
     * @dataProvider provideOnKernelController
     */
    public function testOnKernelController(string $type, string $value, $expectedValue): void
    {
        $queryParameter = new QueryParameter();
        $queryParameter->name = 'test';
        $queryParameter->type = $type;

        $this->reader
            ->expects($this->once())
            ->method('getMethodAnnotations')
            ->willReturn([$queryParameter]);

        $controllerEvent = new ControllerEvent(
            $this->createMock(KernelInterface::class),
            [$this, 'testOnKernelController'],
            $request = new Request(),
            constant(HttpKernelInterface::class.'::MASTER_REQUEST') ?: constant(HttpKernelInterface::class.'::MAIN_REQUEST')
        );

        $request->query->set('test', $value);

        $this->service->onKernelController($controllerEvent);

        $this->assertSame(
            $expectedValue,
            $request->attributes->get('test')
        );
    }
}

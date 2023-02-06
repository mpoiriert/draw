<?php

namespace Draw\Component\OpenApi\Tests\EventListener;

use Draw\Component\OpenApi\EventListener\RequestQueryParameterFetcherListener;
use Draw\Component\OpenApi\Schema\QueryParameter;
use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpKernel\KernelInterface;

/**
 * @covers \Draw\Component\OpenApi\EventListener\RequestQueryParameterFetcherListener
 */
class RequestQueryParameterFetcherListenerTest extends TestCase
{
    private RequestQueryParameterFetcherListener $object;

    protected function setUp(): void
    {
        $this->object = new RequestQueryParameterFetcherListener();
    }

    public function testConstruct(): void
    {
        static::assertInstanceOf(
            EventSubscriberInterface::class,
            $this->object
        );
    }

    public function testSubscribedEvents(): void
    {
        static::assertSame(
            [
                KernelEvents::CONTROLLER => ['onKernelController', 5],
            ],
            $this->object::getSubscribedEvents()
        );
    }

    public function testOnKernelControllerUnParsableController(): void
    {
        $event = new ControllerEvent(
            $this->createMock(HttpKernelInterface::class),
            'gettype',
            $request = new Request(),
            null
        );

        $this->object
            ->onKernelController($event);

        static::assertEmpty($request->attributes->all());
    }

    public function testOnKernelControllerInvoke(): void
    {
        $event = new ControllerEvent(
            $this->createMock(HttpKernelInterface::class),
            $this,
            $request = new Request(),
            null
        );

        $request->query->set('test', $value = uniqid('value-'));

        $this->object->onKernelController($event);

        static::assertSame($value, $request->attributes->get('test'));
    }

    public function testOnKernelControllerAttributeConflict(): void
    {
        $event = new ControllerEvent(
            $this->createMock(HttpKernelInterface::class),
            $this,
            $request = new Request(),
            null
        );

        $request->attributes->set($name = 'test', uniqid('value-'));

        $request->attributes->set(
            '_route',
            $route = uniqid('route-')
        );

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage(
            sprintf(
                'QueryParameterFetcherSubscriber parameter conflicts with a path parameter [%s] for route [%s]',
                $name,
                $route
            )
        );

        $this->object->onKernelController($event);
    }

    public function provideOnKernelController(): iterable
    {
        foreach ((new \ReflectionObject($this))->getMethods() as $reflectionMethod) {
            if (str_starts_with($reflectionMethod->getName(), 'actionTest')) {
                $parameters = $reflectionMethod->getParameters();

                yield $reflectionMethod->getName() => [
                    $reflectionMethod->getName(),
                    $parameters[0]->getDefaultValue(),
                    $parameters[1]->getDefaultValue(),
                ];
            }
        }
    }

    public function actionTestDefault(
        mixed $value = 'expected-value',
        #[QueryParameter] mixed $expectedValue = 'expected-value',
    ): void {
    }

    public function actionTestString(
        mixed $value = 'expected-value',
        #[QueryParameter(type: 'string')] mixed $expectedValue = 'expected-value',
    ): void {
    }

    public function actionTestInt(
        mixed $value = '10',
        #[QueryParameter(type: 'integer')] mixed $expectedValue = 10,
    ): void {
    }

    public function actionTestNumber(
        mixed $value = '10.12',
        #[QueryParameter(type: 'number')] mixed $expectedValue = 10.12,
    ): void {
    }

    public function actionTestArrayCsv(
        mixed $value = 'test,toto',
        #[QueryParameter(type: 'array', collectionFormat: 'csv')] mixed $expectedValue = ['test', 'toto'],
    ): void {
    }

    public function actionTestArraySsv(
        mixed $value = 'test toto',
        #[QueryParameter(type: 'array', collectionFormat: 'ssv')] mixed $expectedValue = ['test', 'toto'],
    ): void {
    }

    public function actionTestArrayTsv(
        mixed $value = "test\ttoto",
        #[QueryParameter(type: 'array', collectionFormat: 'tsv')] mixed $expectedValue = ['test', 'toto'],
    ): void {
    }

    public function actionTestArrayPipes(
        mixed $value = 'test|toto',
        #[QueryParameter(type: 'array', collectionFormat: 'pipes')] mixed $expectedValue = ['test', 'toto'],
    ): void {
    }

    public function actionTestBoolean0False(
        mixed $value = '0',
        #[QueryParameter(type: 'boolean')] mixed $expectedValue = false,
    ): void {
    }

    public function actionTestBoolean1True(
        mixed $value = '1',
        #[QueryParameter(type: 'boolean')] mixed $expectedValue = true,
    ): void {
    }

    public function actionTestBooleanTrueTrue(
        mixed $value = 'true',
        #[QueryParameter(type: 'boolean')] mixed $expectedValue = true,
    ): void {
    }

    public function actionTestBooleanFalseFalse(
        mixed $value = 'false',
        #[QueryParameter(type: 'boolean')] mixed $expectedValue = false,
    ): void {
    }

    /**
     * @dataProvider provideOnKernelController
     */
    public function testOnKernelController(string $methodName, mixed $value, mixed $expectedValue): void
    {
        $controllerEvent = new ControllerEvent(
            $this->createMock(KernelInterface::class),
            [$this, $methodName],
            $request = new Request(),
            HttpKernelInterface::MAIN_REQUEST
        );

        $request->query->set('expectedValue', $value);

        $this->object->onKernelController($controllerEvent);

        static::assertSame(
            $expectedValue,
            $request->attributes->get('expectedValue')
        );
    }

    public function testOnKernelControllerInvalidArrayCollectionFormat(): void
    {
        $controllerEvent = new ControllerEvent(
            $this->createMock(KernelInterface::class),
            [$this, 'multiAction'],
            $request = new Request(),
            HttpKernelInterface::MAIN_REQUEST
        );

        $request->query->set('test', uniqid());

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage(
            sprintf('Unsupported collection format [%s]', 'multi')
        );

        $this->object->onKernelController($controllerEvent);
    }

    public function __invoke(
        #[QueryParameter] string $test
    ): void {
    }

    public function multiAction(
        #[QueryParameter(type: 'array', collectionFormat: 'multi')] string $test
    ): void {
    }
}

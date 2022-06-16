<?php

namespace Draw\Component\OpenApi\Tests\EventListener;

use Doctrine\Common\Annotations\Reader;
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

    private Reader $reader;

    protected function setUp(): void
    {
        $this->object = new RequestQueryParameterFetcherListener(
            $this->reader = $this->createMock(Reader::class)
        );
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
        $this->reader
            ->expects(static::never())
            ->method('getMethodAnnotation');

        $event = new ControllerEvent(
            $this->createMock(HttpKernelInterface::class),
            'gettype',
            new Request(),
            null
        );

        $this->object
            ->onKernelController($event);
    }

    public function testOnKernelControllerInvoke(): void
    {
        $event = new ControllerEvent(
            $this->createMock(HttpKernelInterface::class),
            $this,
            $request = new Request(),
            null
        );

        $this->reader
            ->expects(static::once())
            ->method('getMethodAnnotations')
            ->with(
                static::callback(function (\ReflectionMethod $method) {
                    $this->assertSame(static::class, $method->getDeclaringClass()->name);
                    $this->assertSame('__invoke', $method->name);

                    return true;
                })
            )
            ->willReturn([
                (object) [],
                $annotation = new QueryParameter(),
            ]);

        $annotation->name = uniqid('name-');
        $request->query->set($annotation->name, $value = uniqid('value-'));

        $this->object->onKernelController($event);

        static::assertSame($value, $request->attributes->get($annotation->name));
        static::assertSame(
            [$annotation],
            $request->attributes->get('_draw_query_parameters_validation')
        );
    }

    public function testOnKernelControllerAttributeConflict(): void
    {
        $event = new ControllerEvent(
            $this->createMock(HttpKernelInterface::class),
            $this,
            $request = new Request(),
            null
        );

        $request->attributes->set(
            $name = uniqid('name-'),
            uniqid('value')
        );

        $request->attributes->set(
            '_route',
            $route = uniqid('route-')
        );

        $this->reader
            ->expects(static::once())
            ->method('getMethodAnnotations')
            ->with(
                static::callback(function (\ReflectionMethod $method) {
                    $this->assertSame(static::class, $method->getDeclaringClass()->name);
                    $this->assertSame('__invoke', $method->name);

                    return true;
                })
            )
            ->willReturn([
                (object) [],
                $annotation = new QueryParameter(),
            ]);

        $annotation->name = $name;

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
        $arrayValues = [uniqid('value-1-'), uniqid('value-2-')];

        yield 'default' => ['', $value = uniqid('value'), $value];
        yield 'string' => ['string', $value = uniqid('value'), $value];
        yield 'integer' => ['integer', (string) $value = rand(\PHP_INT_MIN, \PHP_INT_MAX), $value];
        yield 'number' => ['number', (string) ($value = rand(-50, 50) + 0.12), $value];
        yield 'array-csv' => ['array', implode(',', $arrayValues), $arrayValues, 'csv'];
        yield 'array-ssv' => ['array', implode(' ', $arrayValues), $arrayValues, 'ssv'];
        yield 'array-tsv' => ['array', implode("\t", $arrayValues), $arrayValues, 'tsv'];
        yield 'array-pipes' => ['array', implode('|', $arrayValues), $arrayValues, 'pipes'];
        yield 'boolean-0-false' => ['boolean', '0', false];
        yield 'boolean-1-true' => ['boolean', '1', true];
        yield 'boolean-true-true' => ['boolean', 'true', true];
        yield 'boolean-false-false' => ['boolean', 'false', false];
    }

    /**
     * @dataProvider provideOnKernelController
     *
     * @param mixed $expectedValue
     */
    public function testOnKernelController(string $type, string $value, $expectedValue, ?string $arraySeparator = null): void
    {
        $queryParameter = new QueryParameter();
        $queryParameter->name = 'test';
        $queryParameter->type = $type;
        if ('array' === $type) {
            $queryParameter->collectionFormat = $arraySeparator;
        }

        $this->reader
            ->expects(static::once())
            ->method('getMethodAnnotations')
            ->willReturn([$queryParameter]);

        $controllerEvent = new ControllerEvent(
            $this->createMock(KernelInterface::class),
            [$this, 'testOnKernelController'],
            $request = new Request(),
            HttpKernelInterface::MAIN_REQUEST
        );

        $request->query->set('test', $value);

        $this->object->onKernelController($controllerEvent);

        static::assertSame(
            $expectedValue,
            $request->attributes->get('test')
        );
    }

    public function testOnKernelControllerInvalidArrayCollectionFormat(): void
    {
        $queryParameter = new QueryParameter();
        $queryParameter->name = 'test';
        $queryParameter->type = 'array';
        $queryParameter->collectionFormat = 'multi';

        $this->reader
            ->expects(static::once())
            ->method('getMethodAnnotations')
            ->willReturn([$queryParameter]);

        $controllerEvent = new ControllerEvent(
            $this->createMock(KernelInterface::class),
            [$this, 'testOnKernelController'],
            $request = new Request(),
            HttpKernelInterface::MAIN_REQUEST
        );

        $request->query->set('test', uniqid());

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage(
            sprintf('Unsupported collection format [%s]', $queryParameter->collectionFormat)
        );

        $this->object->onKernelController($controllerEvent);
    }

    public function __invoke()
    {
    }
}

<?php

namespace Draw\Component\Log\Tests\Symfony\EventListener;

use Draw\Component\Log\Symfony\EventListener\SlowRequestLoggerListener;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestMatcherInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\TerminateEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;

/**
 * @internal
 */
class SlowRequestLoggerTest extends TestCase
{
    public function testGetSubscribedEvents(): void
    {
        $requestMatcher = static::createStub(RequestMatcherInterface::class);

        $object = new SlowRequestLoggerListener(
            static::createStub(LoggerInterface::class),
            [
                5000 => [$requestMatcher],
                2000 => [$requestMatcher],
            ]
        );

        static::assertSame(
            [
                TerminateEvent::class => ['onKernelTerminate', 2048],
            ],
            $object::getSubscribedEvents()
        );
    }

    public function testOnKernelTerminateMatch(): void
    {
        $durations = [];

        $requestMatcher = $this->createMock(RequestMatcherInterface::class);

        $object = new SlowRequestLoggerListener(
            $logger = $this->createMock(LoggerInterface::class),
            [
                ($durations[] = 5000) => [$requestMatcher],
                ($durations[] = 2000) => [$requestMatcher],
            ]
        );

        $requestMatcher
            ->expects(static::exactly(2))
            ->method('matches')
            ->with($request = new Request())
            ->willReturn(true)
        ;

        $event = new TerminateEvent(
            static::createStub(HttpKernelInterface::class),
            $request,
            new Response()
        );

        $logger
            ->expects(static::once())
            ->method('log')
            ->with(
                LogLevel::WARNING,
                'Response time too slow ({duration} milliseconds) for {url}',
                static::callback(function (array $parameter) use ($request, $durations) {
                    $this->assertSame(
                        $parameter['url'],
                        $request->getRequestUri()
                    );

                    $this->assertEqualsWithDelta(
                        (microtime(true) - (float) $request->server->get('REQUEST_TIME_FLOAT')) * 1000,
                        $parameter['duration'],
                        50
                    );

                    $this->assertSame(
                        min($durations),
                        $parameter['durationThreshold'],
                    );

                    return true;
                })
            )
        ;

        $request->server->set('REQUEST_TIME_FLOAT', microtime(true) - (max($durations) / 1000) - 1);

        $object->onKernelTerminate($event);
    }

    public function testOnKernelTerminateNoMatch(): void
    {
        $durations = [];

        $requestMatcher = $this->createMock(RequestMatcherInterface::class);

        $object = new SlowRequestLoggerListener(
            $logger = $this->createMock(LoggerInterface::class),
            [
                ($durations[] = 5000) => [$requestMatcher],
                ($durations[] = 2000) => [$requestMatcher],
            ]
        );

        $requestMatcher
            ->expects(static::exactly(2))
            ->method('matches')
            ->with($request = new Request())
            ->willReturn(false)
        ;

        $event = new TerminateEvent(
            static::createStub(HttpKernelInterface::class),
            $request,
            new Response()
        );

        $logger
            ->expects(static::never())
            ->method('log')
        ;

        $request->server->set('REQUEST_TIME_FLOAT', microtime(true) - (max($durations) / 1000) - 1);

        $object->onKernelTerminate($event);
    }
}

<?php

namespace Draw\Component\Log\Tests\Symfony\EventListener;

use Draw\Component\Log\Symfony\EventListener\SlowRequestLoggerListener;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestMatcherInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\TerminateEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;

class SlowRequestLoggerTest extends TestCase
{
    private SlowRequestLoggerListener $object;

    /**
     * @var LoggerInterface&MockObject
     */
    private LoggerInterface $logger;

    /**
     * @var RequestMatcherInterface&MockObject
     */
    private RequestMatcherInterface $requestMatcher;

    private array $durations = [];

    protected function setUp(): void
    {
        $this->requestMatcher = $this->createMock(RequestMatcherInterface::class);

        $this->object = new SlowRequestLoggerListener(
            $this->logger = $this->createMock(LoggerInterface::class),
            [
                ($this->durations[] = 5000) => [$this->requestMatcher],
                ($this->durations[] = 2000) => [$this->requestMatcher],
            ]
        );
    }

    public function testConstruct(): void
    {
        static::assertInstanceOf(
            EventSubscriberInterface::class,
            $this->object
        );
    }

    public function testGetSubscribedEvents(): void
    {
        static::assertSame(
            [
                TerminateEvent::class => ['onKernelTerminate', 2048],
            ],
            $this->object::getSubscribedEvents()
        );
    }

    public function testOnKernelTerminateMatch(): void
    {
        $this->requestMatcher
            ->expects(static::exactly(2))
            ->method('matches')
            ->with($request = new Request())
            ->willReturn(true);

        $event = new TerminateEvent(
            $this->createMock(HttpKernelInterface::class),
            $request,
            new Response()
        );

        $this->logger
            ->expects(static::once())
            ->method('log')
            ->with(
                LogLevel::WARNING,
                'Response time too slow ({duration} milliseconds) for {url}',
                static::callback(function (array $parameter) use ($request) {
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
                        min($this->durations),
                        $parameter['durationThreshold'],
                    );

                    return true;
                })
            );

        $request->server->set('REQUEST_TIME_FLOAT', microtime(true) - (max($this->durations) / 1000) - 1);

        $this->object->onKernelTerminate($event);
    }

    public function testOnKernelTerminateNoMatch(): void
    {
        $this->requestMatcher
            ->expects(static::exactly(2))
            ->method('matches')
            ->with($request = new Request())
            ->willReturn(false);

        $event = new TerminateEvent(
            $this->createMock(HttpKernelInterface::class),
            $request,
            new Response()
        );

        $this->logger
            ->expects(static::never())
            ->method('log');

        $request->server->set('REQUEST_TIME_FLOAT', microtime(true) - (max($this->durations) / 1000) - 1);

        $this->object->onKernelTerminate($event);
    }
}

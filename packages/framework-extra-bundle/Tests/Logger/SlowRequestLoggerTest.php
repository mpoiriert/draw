<?php

namespace Draw\Bundle\FrameworkExtraBundle\Tests\Logger;

use Draw\Bundle\FrameworkExtraBundle\Logger\SlowRequestLogger;
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
    private SlowRequestLogger $service;

    private LoggerInterface $logger;

    private RequestMatcherInterface $requestMatcher;

    private array $durations = [];

    public function setUp(): void
    {
        $this->requestMatcher = $this->createMock(RequestMatcherInterface::class);

        $this->service = new SlowRequestLogger(
            $this->logger = $this->createMock(LoggerInterface::class),
            [
                ($this->durations[] = 5000) => [$this->requestMatcher],
                ($this->durations[] = 2000) => [$this->requestMatcher],
            ]
        );
    }

    public function testConstruct(): void
    {
        $this->assertInstanceOf(
            EventSubscriberInterface::class,
            $this->service
        );
    }

    public function testGetSubscribedEvents(): void
    {
        $this->assertSame(
            [
                TerminateEvent::class => ['onKernelTerminate', 2048],
            ],
            $this->service::getSubscribedEvents()
        );
    }

    public function testOnKernelTerminateMatch(): void
    {
        $_SERVER['REQUEST_TIME_FLOAT'] = microtime(true) - (max($this->durations) / 1000) - 1;

        $this->requestMatcher
            ->expects($this->exactly(2))
            ->method('matches')
            ->with($request = new Request())
            ->willReturn(true);

        $event = new TerminateEvent(
            $this->createMock(HttpKernelInterface::class),
            $request,
            new Response()
        );

        $this->logger
            ->expects($this->once())
            ->method('log')
            ->with(
                LogLevel::WARNING,
                'Response time too slow ({duration} milliseconds) for {url}',
                $this->callback(function (array $parameter) use ($request) {
                    $this->assertSame(
                        $parameter['url'],
                        $request->getRequestUri()
                    );

                    $this->assertEqualsWithDelta(
                        (microtime(true) - (float) $_SERVER['REQUEST_TIME_FLOAT']) * 1000,
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

        $this->service->onKernelTerminate($event);
    }

    public function testOnKernelTerminateNoMatch(): void
    {
        $_SERVER['REQUEST_TIME_FLOAT'] = microtime(true);

        $this->requestMatcher
            ->expects($this->exactly(2))
            ->method('matches')
            ->with($request = new Request())
            ->willReturn(false);

        $event = new TerminateEvent(
            $this->createMock(HttpKernelInterface::class),
            $request,
            new Response()
        );

        $this->logger
            ->expects($this->never())
            ->method('log');

        $this->service->onKernelTerminate($event);
    }
}

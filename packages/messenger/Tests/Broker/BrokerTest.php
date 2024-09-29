<?php

namespace Draw\Component\Messenger\Tests\Broker;

use Draw\Component\Messenger\Broker\Broker;
use Draw\Component\Messenger\Broker\Event\BrokerRunningEvent;
use Draw\Component\Messenger\Broker\Event\BrokerStartedEvent;
use Draw\Component\Messenger\Broker\Event\NewConsumerProcessEvent;
use Draw\Component\Tester\MockTrait;
use Draw\Contracts\Process\ProcessFactoryInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Process\Process;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

#[CoversClass(Broker::class)]
class BrokerTest extends TestCase
{
    use MockTrait;

    private Broker $service;

    private string $context;

    private string $consolePath;

    private ProcessFactoryInterface&MockObject $processFactory;

    private EventDispatcherInterface&MockObject $eventDispatcher;

    protected function setUp(): void
    {
        $this->service = new Broker(
            $this->context = uniqid('context-'),
            $this->consolePath = uniqid('console/bin-'),
            $this->processFactory = $this->createMock(ProcessFactoryInterface::class),
            $this->eventDispatcher = $this->createMock(EventDispatcherInterface::class)
        );
    }

    public function testGetContext(): void
    {
        static::assertSame(
            $this->context,
            $this->service->getContext()
        );
    }

    public function testStart(): void
    {
        $concurrent = 1;
        $timeout = random_int(1, 10);
        $receiver = uniqid('receiver-');

        $this->eventDispatcher
            ->expects(static::exactly($concurrent * 4))
            ->method('dispatch')
            ->with(
                ...static::withConsecutive(
                    [
                        static::callback(function (BrokerStartedEvent $event) use ($concurrent, $timeout) {
                            $this->assertSame(
                                $this->service,
                                $event->getBroker()
                            );

                            $this->assertSame(
                                $concurrent,
                                $event->getConcurrent()
                            );

                            $this->assertSame(
                                $timeout,
                                $event->getTimeout()
                            );

                            return true;
                        }),
                    ],
                    [
                        static::callback(function (BrokerRunningEvent $event) {
                            $this->assertSame(
                                $this->service,
                                $event->getBroker()
                            );

                            return true;
                        }),
                    ],
                    [
                        static::callback(function (NewConsumerProcessEvent $event) use ($receiver) {
                            static::assertSame(
                                $this->context,
                                $event->getContext()
                            );

                            $event->setReceivers([$receiver]);

                            return true;
                        }),
                    ],
                    [
                        static::callback(function (BrokerRunningEvent $event) {
                            $this->assertSame(
                                $this->service,
                                $event->getBroker()
                            );

                            $this->service->stop();

                            return true;
                        }),
                    ],
                )
            )
            ->willReturnArgument(0);

        $this->processFactory
            ->expects(static::exactly($concurrent))
            ->method('create')
            ->with(
                [
                    $this->consolePath,
                    'messenger:consume',
                    $receiver,
                ],
                null,
                null,
                null,
                null
            )
            ->willReturn($process = $this->createMock(Process::class));

        $process
            ->expects(static::exactly($concurrent))
            ->method('start');

        $process
            ->expects(static::exactly($concurrent))
            ->method('isRunning')
            ->willReturn(false);

        $this->service->start($concurrent, $timeout);
    }

    public function testStartWithForceStop(): void
    {
        $concurrent = 2;
        $receiver = uniqid('receiver-');

        $this->eventDispatcher
            ->expects(static::any())
            ->method('dispatch')
            ->with(
                static::callback(function ($event) use ($receiver) {
                    if ($event instanceof NewConsumerProcessEvent) {
                        $event->setReceivers([$receiver]);
                        $this->service->stop(false);
                    }

                    return true;
                })
            )
            ->willReturnArgument(0);

        $this->processFactory
            ->expects(static::exactly($concurrent))
            ->method('create')
            ->with(
                [
                    $this->consolePath,
                    'messenger:consume',
                    $receiver,
                ],
                null,
                null,
                null,
                null
            )
            ->willReturn($process = $this->createMock(Process::class));

        $process
            ->expects(static::exactly($concurrent))
            ->method('start');

        $process
            ->expects(static::exactly(6)) // $concurrent * 3
            ->method('isRunning')
            ->willReturnOnConsecutiveCalls(
                true,
                true,
                true,
                true,
                false,
                true
            );

        $process
            ->expects(static::exactly($concurrent))
            ->method('signal')
            ->with(15)
            ->willReturnSelf();

        $process
            ->expects(static::once())
            ->method('stop')
            ->with(0)
            ->willReturn(0);

        $this->service->start($concurrent, 0);
    }

    public function testStartNoReceiver(): void
    {
        $concurrent = 1;

        $this->processFactory
            ->expects(static::never())
            ->method('create');

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage(\sprintf(
            'You must have at least one receivers. If you do not want to prevent the consumer process to start use the [%s] event method.',
            NewConsumerProcessEvent::class.'::preventStart'
        ));

        $this->service->start($concurrent);
    }

    public function testStartForBuildOptions(): void
    {
        $concurrent = 1;
        $timeout = random_int(1, 10);
        $receiver = uniqid('receiver-');
        $options = [
            'array' => ['value1', 'value2'],
            'null' => null,
            'value' => 'value',
        ];

        $this->eventDispatcher
            ->expects(static::any())
            ->method('dispatch')
            ->with(
                static::callback(function ($event) use ($receiver, $options) {
                    if ($event instanceof NewConsumerProcessEvent) {
                        $event->setReceivers([$receiver]);
                        $event->setOptions($options);
                        // This is to make sure we reach NewConsumerProcessEvent only once.
                        $this->service->stop();
                    }

                    return true;
                })
            )
            ->willReturnArgument(0);

        $this->processFactory
            ->expects(static::exactly($concurrent))
            ->method('create')
            ->with(
                [
                    $this->consolePath,
                    'messenger:consume',
                    $receiver,
                    '--array',
                    'value1',
                    '--array',
                    'value2',
                    '--null',
                    '--value',
                    'value',
                ],
                null,
                null,
                null,
                null
            )
            ->willReturn($process = $this->createMock(Process::class));

        $process
            ->expects(static::exactly($concurrent))
            ->method('start');

        $process
            ->expects(static::exactly($concurrent))
            ->method('isRunning')
            ->willReturn(false);

        $this->service->start($concurrent, $timeout);
    }
}

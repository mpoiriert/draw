<?php

namespace Draw\Component\Messenger\Tests\Broker;

use Draw\Component\Messenger\Broker\Broker;
use Draw\Component\Messenger\Broker\Event\BrokerRunningEvent;
use Draw\Component\Messenger\Broker\Event\BrokerStartedEvent;
use Draw\Component\Messenger\Broker\Event\NewConsumerProcessEvent;
use Draw\Component\Tester\DoubleTrait;
use Draw\Contracts\Process\ProcessFactoryInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Process\Process;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

/**
 * @internal
 */
#[CoversClass(Broker::class)]
class BrokerTest extends TestCase
{
    use DoubleTrait;

    private string $context;

    private string $consolePath;

    protected function setUp(): void
    {
        $this->context = uniqid('context-');
        $this->consolePath = uniqid('console/bin-');
    }

    public function testGetContext(): void
    {
        $service = new Broker(
            $this->context,
            $this->consolePath,
            static::createStub(ProcessFactoryInterface::class),
            static::createStub(EventDispatcherInterface::class)
        );

        static::assertSame(
            $this->context,
            $service->getContext()
        );
    }

    public function testStart(): void
    {
        $service = new Broker(
            $this->context,
            $this->consolePath,
            $processFactory = $this->createMock(ProcessFactoryInterface::class),
            $eventDispatcher = $this->createMock(EventDispatcherInterface::class)
        );

        $concurrent = 1;
        $timeout = random_int(1, 10);
        $receiver = uniqid('receiver-');

        $eventDispatcher
            ->expects(static::exactly($concurrent * 4))
            ->method('dispatch')
            ->with(
                ...static::withConsecutive(
                    [
                        static::callback(function (BrokerStartedEvent $event) use ($service, $concurrent, $timeout) {
                            $this->assertSame(
                                $service,
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
                        static::callback(function (BrokerRunningEvent $event) use ($service) {
                            $this->assertSame(
                                $service,
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
                        static::callback(function (BrokerRunningEvent $event) use ($service) {
                            $this->assertSame(
                                $service,
                                $event->getBroker()
                            );

                            $service->stop();

                            return true;
                        }),
                    ],
                )
            )
            ->willReturnArgument(0)
            ->seal()
        ;

        $processFactory
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
            ->willReturn($process = $this->createMock(Process::class))
            ->seal()
        ;

        $process
            ->expects(static::exactly($concurrent))
            ->method('start')
        ;

        $process
            ->expects(static::exactly($concurrent))
            ->method('isRunning')
            ->willReturn(false)
            ->seal()
        ;

        $service->start($concurrent, $timeout);
    }

    public function testStartWithForceStop(): void
    {
        $service = new Broker(
            $this->context,
            $this->consolePath,
            $processFactory = $this->createMock(ProcessFactoryInterface::class),
            $eventDispatcher = $this->createMock(EventDispatcherInterface::class)
        );

        $concurrent = 2;
        $receiver = uniqid('receiver-');

        $eventDispatcher
            ->expects(static::atLeastOnce())
            ->method('dispatch')
            ->with(
                static::callback(static function ($event) use ($service, $receiver) {
                    if ($event instanceof NewConsumerProcessEvent) {
                        $event->setReceivers([$receiver]);
                        $service->stop(false);
                    }

                    return true;
                })
            )
            ->willReturnArgument(0)
            ->seal()
        ;

        $processFactory
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
            ->willReturn($process = $this->createMock(Process::class))
            ->seal()
        ;

        $process
            ->expects(static::exactly($concurrent))
            ->method('start')
        ;

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
            )
        ;

        $process
            ->expects(static::exactly($concurrent))
            ->method('signal')
            ->with(15)
            ->willReturnSelf()
        ;

        $process
            ->expects(static::once())
            ->method('stop')
            ->with(0)
            ->willReturn(0)
            ->seal()
        ;

        $service->start($concurrent, 0);
    }

    public function testStartNoReceiver(): void
    {
        $service = new Broker(
            $this->context,
            $this->consolePath,
            $processFactory = $this->createMock(ProcessFactoryInterface::class),
            static::createStub(EventDispatcherInterface::class)
        );

        $concurrent = 1;

        $processFactory
            ->expects(static::never())
            ->method('create')
            ->seal()
        ;

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage(\sprintf(
            'You must have at least one receivers. If you do not want to prevent the consumer process to start use the [%s] event method.',
            NewConsumerProcessEvent::class.'::preventStart'
        ));

        $service->start($concurrent);
    }

    public function testStartForBuildOptions(): void
    {
        $service = new Broker(
            $this->context,
            $this->consolePath,
            $processFactory = $this->createMock(ProcessFactoryInterface::class),
            $eventDispatcher = $this->createMock(EventDispatcherInterface::class)
        );

        $concurrent = 1;
        $timeout = random_int(1, 10);
        $receiver = uniqid('receiver-');
        $options = [
            'array' => ['value1', 'value2'],
            'null' => null,
            'value' => 'value',
        ];

        $eventDispatcher
            ->expects(static::atLeastOnce())
            ->method('dispatch')
            ->with(
                static::callback(static function ($event) use ($service, $receiver, $options) {
                    if ($event instanceof NewConsumerProcessEvent) {
                        $event->setReceivers([$receiver]);
                        $event->setOptions($options);
                        // This is to make sure we reach NewConsumerProcessEvent only once.
                        $service->stop();
                    }

                    return true;
                })
            )
            ->willReturnArgument(0)
            ->seal()
        ;

        $processFactory
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
            ->willReturn($process = $this->createMock(Process::class))
            ->seal()
        ;

        $process
            ->expects(static::exactly($concurrent))
            ->method('start')
        ;

        $process
            ->expects(static::exactly($concurrent))
            ->method('isRunning')
            ->willReturn(false)
            ->seal()
        ;

        $service->start($concurrent, $timeout);
    }
}

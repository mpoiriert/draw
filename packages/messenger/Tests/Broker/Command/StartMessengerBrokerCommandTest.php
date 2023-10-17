<?php

namespace Draw\Component\Messenger\Tests\Broker\Command;

use Draw\Component\Core\Reflection\ReflectionAccessor;
use Draw\Component\Messenger\Broker\Command\StartMessengerBrokerCommand;
use Draw\Component\Messenger\Broker\Event\BrokerStartedEvent;
use Draw\Component\Tester\Application\CommandDataTester;
use Draw\Component\Tester\Application\CommandTestTrait;
use Draw\Contracts\Process\ProcessFactoryInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Exception\InvalidOptionException;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\EventDispatcher\EventDispatcher;

#[CoversClass(StartMessengerBrokerCommand::class)]
class StartMessengerBrokerCommandTest extends TestCase
{
    use CommandTestTrait;

    private ProcessFactoryInterface $processFactory;

    private EventDispatcher $eventDispatcher;

    private string $consolePath;

    public function createCommand(): Command
    {
        return new StartMessengerBrokerCommand(
            $this->consolePath = uniqid('console-path-'),
            $this->processFactory = $this->createMock(ProcessFactoryInterface::class),
            $this->eventDispatcher = new EventDispatcher()
        );
    }

    public function getCommandName(): string
    {
        return 'draw:messenger:start-broker';
    }

    public static function provideTestArgument(): iterable
    {
        return [];
    }

    public static function provideTestOption(): iterable
    {
        yield [
            'context',
            null,
            InputOption::VALUE_REQUIRED,
            'default',
        ];

        yield [
            'concurrent',
            null,
            InputOption::VALUE_REQUIRED,
            1,
        ];

        yield [
            'timeout',
            null,
            InputOption::VALUE_REQUIRED,
            10,
        ];
    }

    public function testExecuteInvalidConcurrent(): void
    {
        $concurrent = random_int(\PHP_INT_MIN, 0);
        $this->expectException(InvalidOptionException::class);
        $this->expectExceptionMessage('Concurrent value ['.$concurrent.'] is invalid. Must be 1 or greater');

        $this->execute(['--concurrent' => $concurrent]);
    }

    public function testExecuteInvalidTimeout(): void
    {
        $timeout = random_int(\PHP_INT_MIN, -1);
        $this->expectException(InvalidOptionException::class);
        $this->expectExceptionMessage('Timeout value ['.$timeout.'] is invalid. Must be 0 or greater');

        $this->execute(['--timeout' => $timeout]);
    }

    public function testExecute(): void
    {
        $concurrent = random_int(1, 10);
        $timeout = random_int(1, 10);
        $context = uniqid('context-');

        $this->eventDispatcher->addListener(
            BrokerStartedEvent::class,
            function (BrokerStartedEvent $event) use ($concurrent, $timeout, $context): void {
                $this->assertSame(
                    $context,
                    $event->getBroker()->getContext()
                );

                $this->assertSame(
                    $concurrent,
                    $event->getConcurrent()
                );

                $this->assertSame(
                    $timeout,
                    $event->getTimeout()
                );

                $broker = $event->getBroker();

                $this->assertSame(
                    $this->processFactory,
                    ReflectionAccessor::getPropertyValue($broker, 'processFactory')
                );

                $this->assertSame(
                    $this->eventDispatcher,
                    ReflectionAccessor::getPropertyValue($broker, 'eventDispatcher')
                );

                $this->assertSame(
                    $this->consolePath,
                    ReflectionAccessor::getPropertyValue($broker, 'consolePath')
                );

                $broker->stop();
            }
        );

        $this->execute([
            '--context' => $context,
            '--concurrent' => $concurrent,
            '--timeout' => $timeout,
        ])->test(
            CommandDataTester::create(
                0,
                [
                    '[OK] Broker starting.',
                    '! [NOTE] Concurrency '.$concurrent,
                    '! [NOTE] Timeout '.$timeout,
                    '[OK] Broker stopped. ',
                ]
            )
        );
    }
}

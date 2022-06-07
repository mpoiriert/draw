<?php

namespace Draw\Component\Messenger\Tests\Command;

use DateTime;
use Draw\Component\Messenger\Command\PurgeExpiredMessageCommand;
use Draw\Component\Messenger\Transport\ObsoleteMessageAwareInterface;
use Draw\Component\Tester\Application\CommandDataTester;
use Draw\Component\Tester\Application\CommandTestTrait;
use Draw\Component\Tester\MockBuilderTrait;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use RuntimeException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Messenger\Transport\TransportInterface;

/**
 * @covers \Draw\Component\Messenger\Command\PurgeExpiredMessageCommand
 */
class PurgeExpiredMessageCommandTest extends TestCase
{
    use CommandTestTrait;
    use MockBuilderTrait;

    /** @var ContainerInterface|MockObject */
    private ContainerInterface $container;

    private array $transportNames;

    public function createCommand(): Command
    {
        return new PurgeExpiredMessageCommand(
            $this->container = $this->createMock(ContainerInterface::class),
            $this->transportNames = [uniqid('transport-1-'), uniqid('transport-2-')]
        );
    }

    public function getCommandName(): string
    {
        return 'draw:messenger:purge-obsolete-messages';
    }

    public function provideTestArgument(): iterable
    {
        yield [
            'transport',
            InputArgument::OPTIONAL,
        ];
    }

    public function provideTestOption(): iterable
    {
        yield [
            'delay',
            null,
            InputOption::VALUE_OPTIONAL,
            '-1 month',
        ];
    }

    public function testExecuteInvalidTransport(): void
    {
        $this->container
            ->expects($this->once())
            ->method('has')
            ->with($transport = uniqid('transport-invalid-'))
            ->willReturn(false);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage(sprintf('The "%s" transport does not exist.', $transport));

        $this->execute(['transport' => $transport], []);
    }

    public function testExecute(): void
    {
        $this->container
            ->expects($this->exactly(2))
            ->method('get')
            ->withConsecutive(
                [$this->transportNames[0]],
                [$this->transportNames[1]]
            )
            ->willReturnOnConsecutiveCalls(
                $transport1 = $this->createMock(ObsoleteMessageAwareInterface::class),
                $transport2 = $this->createMockWithExtraMethods(
                    TransportInterface::class,
                    ['purgeObsoleteMessages']
                )
            );

        $transport1
            ->expects($this->once())
            ->method('purgeObsoleteMessages')
            ->with(
                $this->equalToWithDelta(new DateTime('- 1 month'), 1)
            )
            ->willReturn($count = rand(1, 10));

        $transport2
            ->expects($this->never())
            ->method('purgeObsoleteMessages');

        $this->execute([], [])
            ->test(
                CommandDataTester::create(
                    0,
                    [
                        sprintf(
                            'The "%s" transport was purge successfully of "%s" message(s).',
                            $this->transportNames[0],
                            $count
                        ),
                        sprintf(
                            'The "%s" transport does not support purge obsolete messages.',
                            $this->transportNames[1]
                        ),
                    ]
                )
            );
    }

    public function testExecuteWithInputs(): void
    {
        $this->container
            ->expects($this->once())
            ->method('has')
            ->with($transportName = $this->transportNames[0])
            ->willReturn(true);

        $this->container
            ->expects($this->once())
            ->method('get')
            ->with($transportName)
            ->willReturn(
                $transport = $this->createMock(ObsoleteMessageAwareInterface::class),
            );

        $delay = '- 4 months';

        $transport
            ->expects($this->once())
            ->method('purgeObsoleteMessages')
            ->with(
                $this->equalToWithDelta(new DateTime($delay), 1)
            )
            ->willReturn($count = rand(1, 10));

        $this->execute(['transport' => $transportName, '--delay' => $delay], [])
            ->test(
                CommandDataTester::create(
                    0,
                    [
                        sprintf(
                            'The "%s" transport was purge successfully of "%s" message(s).',
                            $this->transportNames[0],
                            $count
                        ),
                    ]
                )
            );
    }
}

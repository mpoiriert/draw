<?php

namespace Draw\Component\Messenger\Tests\Expirable\Command;

use Draw\Component\Messenger\Expirable\Command\PurgeExpiredMessageCommand;
use Draw\Component\Messenger\Expirable\PurgeableTransportInterface;
use Draw\Component\Messenger\Searchable\TransportRepository;
use Draw\Component\Tester\Application\CommandDataTester;
use Draw\Component\Tester\Application\CommandTestTrait;
use Draw\Component\Tester\MockBuilderTrait;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Messenger\Transport\TransportInterface;

/**
 * @covers \Draw\Component\Messenger\Expirable\Command\PurgeExpiredMessageCommand
 */
class PurgeExpiredMessageCommandTest extends TestCase
{
    use CommandTestTrait;
    use MockBuilderTrait;

    /** @var TransportRepository|MockObject */
    private TransportRepository $transportRepository;

    public function createCommand(): Command
    {
        return new PurgeExpiredMessageCommand(
            $this->transportRepository = $this->createMock(TransportRepository::class),
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
        $this->transportRepository
            ->expects(static::once())
            ->method('has')
            ->with($transport = uniqid('transport-invalid-'))
            ->willReturn(false);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage(sprintf('The "%s" transport does not exist.', $transport));

        $this->execute(['transport' => $transport], []);
    }

    public function testExecute(): void
    {
        $this->transportRepository
            ->expects(static::once())
            ->method('getTransportNames')
            ->willReturn($transportNames = [uniqid('transport1-'), uniqid('transport2-')]);

        $this->transportRepository
            ->expects(static::exactly(2))
            ->method('get')
            ->withConsecutive(
                [$transportNames[0]],
                [$transportNames[1]]
            )
            ->willReturnOnConsecutiveCalls(
                $transport1 = $this->createMock(PurgeableTransportInterface::class),
                $transport2 = $this->createMockWithExtraMethods(
                    TransportInterface::class,
                    ['purgeObsoleteMessages']
                )
            );

        $transport1
            ->expects(static::once())
            ->method('purgeObsoleteMessages')
            ->with(
                static::equalToWithDelta(new \DateTime('- 1 month'), 1)
            )
            ->willReturn($count = rand(1, 10));

        $transport2
            ->expects(static::never())
            ->method('purgeObsoleteMessages');

        $this->execute([], [])
            ->test(
                CommandDataTester::create(
                    0,
                    [
                        sprintf(
                            'The "%s" transport was purge successfully of "%s" message(s).',
                            $transportNames[0],
                            $count
                        ),
                        sprintf(
                            'The "%s" transport does not support purge obsolete messages.',
                            $transportNames[1]
                        ),
                    ]
                )
            );
    }

    public function testExecuteWithInputs(): void
    {
        $this->transportRepository
            ->expects(static::once())
            ->method('has')
            ->with($transportName = uniqid('transport-'))
            ->willReturn(true);

        $this->transportRepository
            ->expects(static::once())
            ->method('get')
            ->with($transportName)
            ->willReturn(
                $transport = $this->createMock(PurgeableTransportInterface::class),
            );

        $delay = '- 4 months';

        $transport
            ->expects(static::once())
            ->method('purgeObsoleteMessages')
            ->with(
                static::equalToWithDelta(new \DateTime($delay), 1)
            )
            ->willReturn($count = rand(1, 10));

        $this->execute(['transport' => $transportName, '--delay' => $delay], [])
            ->test(
                CommandDataTester::create(
                    0,
                    [
                        sprintf(
                            'The "%s" transport was purge successfully of "%s" message(s).',
                            $transportName,
                            $count
                        ),
                    ]
                )
            );
    }
}

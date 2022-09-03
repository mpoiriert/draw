<?php

namespace Draw\Component\Console\Tests\Output;

use Draw\Component\Console\Output\BufferedConsoleOutput;
use Draw\Component\Tester\MockTrait;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Formatter\OutputFormatterInterface;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @covers \Draw\Component\Console\Output\BufferedConsoleOutput
 */
class BufferedConsoleOutputTest extends TestCase
{
    use MockTrait;
    private BufferedConsoleOutput $object;

    protected function setUp(): void
    {
        $this->object = new BufferedConsoleOutput(OutputInterface::VERBOSITY_NORMAL);
    }

    public function testConstruct(): void
    {
        static::assertInstanceOf(
            ConsoleOutput::class,
            $this->object
        );
    }

    public function testSetDecorated(): void
    {
        $formatter = $this->mockProperty(
            $this->object,
            'formatter',
            OutputFormatterInterface::class
        );

        $formatter
            ->expects(static::exactly(3))
            ->method('isDecorated')
            ->willReturnOnConsecutiveCalls(false, true, true);

        $formatter
            ->expects(static::once())
            ->method('setDecorated')
            ->with(true);

        $this->object->setDecorated(true);

        // Call twice to make sure formatter the call expectations above
        $this->object->setDecorated(true);
    }

    public function testSetFormatter(): void
    {
        // This is to test we are not in a infinite loop
        $this->object->setFormatter($this->createMock(OutputFormatterInterface::class));

        $this->addToAssertionCount(1);
    }

    public function testSetVerbosity(): void
    {
        // This is to test we are not in a infinite loop
        $this->object->setVerbosity(OutputInterface::VERBOSITY_VERY_VERBOSE);

        $this->addToAssertionCount(1);
    }

    public function testFetch(): void
    {
        static::assertSame('', $this->object->fetch());

        $message = uniqid('message-');

        $this->object->write($message, true);

        static::assertSame(
            $message.\PHP_EOL,
            $this->object->fetch()
        );

        static::assertSame('', $this->object->fetch());
    }
}

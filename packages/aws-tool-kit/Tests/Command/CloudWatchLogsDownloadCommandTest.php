<?php

namespace Draw\Component\AwsToolKit\Tests\Command;

use Aws\CloudWatchLogs\CloudWatchLogsClient;
use Draw\Component\AwsToolKit\Command\CloudWatchLogsDownloadCommand;
use Draw\Component\Core\Reflection\ReflectionAccessor;
use Draw\Component\Tester\Application\CommandDataTester;
use Draw\Component\Tester\Application\CommandTestTrait;
use Draw\Component\Tester\MockTrait;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

#[CoversClass(CloudWatchLogsDownloadCommand::class)]
class CloudWatchLogsDownloadCommandTest extends TestCase
{
    use CommandTestTrait;
    use MockTrait;

    private CloudWatchLogsClient&MockObject $cloudWatchLogsClient;

    protected function setUp(): void
    {
        $this->cloudWatchLogsClient = $this
            ->getMockBuilder(CloudWatchLogsClient::class)
            ->disableOriginalConstructor()
            ->disableOriginalClone()
            ->disableArgumentCloning()
            ->disallowMockingUnknownTypes()
            ->addMethods(['getLogEvents'])
            ->getMock();

        $this->command = new CloudWatchLogsDownloadCommand($this->cloudWatchLogsClient);
    }

    public function getCommandName(): string
    {
        return 'draw:aws:cloud-watch-logs:download';
    }

    public static function provideTestArgument(): iterable
    {
        yield ['logGroupName', InputArgument::REQUIRED, null];
        yield ['logStreamName', InputArgument::REQUIRED, null];
        yield ['output', InputArgument::REQUIRED, null];
    }

    public static function provideTestOption(): iterable
    {
        yield ['startTime', null, InputOption::VALUE_REQUIRED, '- 1 days'];
        yield ['endTime', null, InputOption::VALUE_REQUIRED, 'now'];
        yield [
            'fileMode',
            null,
            InputOption::VALUE_REQUIRED,
            'w+',
        ];
    }

    public function testExecuteNoCloudWatchLogsClientService(): void
    {
        ReflectionAccessor::setPropertyValue(
            $this->command,
            'cloudWatchClient',
            null
        );

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Service [Aws\CloudWatchLogs\CloudWatchLogsClient] is required for command [Draw\Component\AwsToolKit\Command\CloudWatchLogsDownloadCommand] to run.');

        $this->execute([
            'logGroupName' => 'group-name',
            'logStreamName' => 'stream-name',
            'output' => sys_get_temp_dir().'/'.uniqid().'.txt',
        ]);
    }

    public function testExecuteNewFile(): void
    {
        $logGroupName = 'group-name';
        $logStreamName = 'stream-name';
        $startTime = new \DateTimeImmutable('2001-01-01 00:00:00');
        $endTime = new \DateTimeImmutable('2001-01-02 00:00:01');
        $output = sys_get_temp_dir().'/'.uniqid().'.txt';
        file_put_contents($output, "Before\n");
        register_shutdown_function('unlink', $output);

        $logEvents = [
            'startFromHead' => true,
            'logGroupName' => $logGroupName,
            'logStreamName' => $logStreamName,
            'startTime' => $startTime->getTimestamp() * 1000,
            'endTime' => $endTime->getTimestamp() * 1000,
        ];

        $this->cloudWatchLogsClient
            ->expects(static::exactly(2))
            ->method('getLogEvents')
            ->with(
                ...static::withConsecutive(
                    [$logEvents],
                    [$logEvents + ['nextToken' => 'next-token']]
                )
            )
            ->willReturnOnConsecutiveCalls(
                [
                    'events' => [
                        ['message' => 'Line 1'],
                    ],
                    'nextForwardToken' => 'next-token',
                ],
                [
                    'events' => [
                        ['message' => 'Line 2'],
                    ],
                    'nextForwardToken' => 'next-token',
                ]
            );

        $this->execute(
            compact('logGroupName', 'logStreamName', 'output')
            + [
                '--startTime' => $startTime->format('Y-m-d H:i:s'),
                '--endTime' => $endTime->format('Y-m-d H:i:s'),
            ]
        )
            ->test(CommandDataTester::create());

        static::assertEquals(
            "Line 1\nLine 2\n",
            file_get_contents($output)
        );
    }

    public function testExecuteAppendFile(): void
    {
        $logGroupName = 'group-name';
        $logStreamName = 'stream-name';
        $startTime = new \DateTimeImmutable('2001-01-01 00:00:00');
        $endTime = new \DateTimeImmutable('2001-01-02 00:00:01');
        $output = sys_get_temp_dir().'/'.uniqid().'.txt';
        file_put_contents($output, "Before\n");
        register_shutdown_function('unlink', $output);

        $this->cloudWatchLogsClient
            ->expects(static::once())
            ->method('getLogEvents')
            ->with(
                [
                    'startFromHead' => true,
                    'logGroupName' => $logGroupName,
                    'logStreamName' => $logStreamName,
                    'startTime' => $startTime->getTimestamp() * 1000,
                    'endTime' => $endTime->getTimestamp() * 1000,
                ]
            )
            ->willReturn(
                [
                    'events' => [
                        ['message' => 'Line 1'],
                    ],
                    'nextForwardToken' => null,
                ]
            );

        $this->execute(
            compact('logGroupName', 'logStreamName', 'output')
            + [
                '--startTime' => $startTime->format('Y-m-d H:i:s'),
                '--endTime' => $endTime->format('Y-m-d H:i:s'),
                '--fileMode' => 'a+',
            ]
        )
            ->test(CommandDataTester::create());

        static::assertEquals(
            "Before\nLine 1\n",
            file_get_contents($output)
        );
    }
}

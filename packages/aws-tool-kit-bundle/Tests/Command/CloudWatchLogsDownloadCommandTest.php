<?php

namespace Draw\Bundle\AwsToolKitBundle\Tests\Command;

use Aws\CloudWatchLogs\CloudWatchLogsClient;
use DateTimeImmutable;
use Draw\Bundle\AwsToolKitBundle\Command\CloudWatchLogsDownloadCommand;
use Draw\Component\Core\Reflection\ReflectionAccessor;
use Draw\Component\Tester\Application\CommandDataTester;
use Draw\Component\Tester\Application\CommandTestCase;
use PHPUnit\Framework\MockObject\MockObject;
use RuntimeException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

/**
 * @covers \Draw\Bundle\AwsToolKitBundle\Command\CloudWatchLogsDownloadCommand
 */
class CloudWatchLogsDownloadCommandTest extends CommandTestCase
{
    /**
     * @var CloudWatchLogsClient|MockObject
     */
    private CloudWatchLogsClient $cloudWatchLogsClient;

    public function getCommandName(): string
    {
        return 'draw:aws:cloud-watch-logs:download';
    }

    public function getCommandDescription(): string
    {
        return 'Download logs from cloud watch locally base on it\'s log group name, log stream name and a start time/end time';
    }

    public function createCommand(): Command
    {
        $this->cloudWatchLogsClient = $this
            ->getMockBuilder(CloudWatchLogsClient::class)
            ->disableOriginalConstructor()
            ->disableOriginalClone()
            ->disableArgumentCloning()
            ->disallowMockingUnknownTypes()
            ->addMethods(['getLogEvents'])
            ->getMock();

        return new CloudWatchLogsDownloadCommand($this->cloudWatchLogsClient);
    }

    public function provideTestArgument(): iterable
    {
        yield ['logGroupName', InputArgument::REQUIRED, 'The log group name', null];
        yield ['logStreamName', InputArgument::REQUIRED, 'The log stream name', null];
        yield ['output', InputArgument::REQUIRED, 'The output file name', null];
    }

    public function provideTestOption(): iterable
    {
        yield ['startTime', null, InputOption::VALUE_REQUIRED, 'Since when the log need to be downloaded.', '- 1 days'];
        yield ['endTime', null, InputOption::VALUE_REQUIRED, 'End time of the download.', 'now'];
        yield [
            'fileMode',
            null,
            InputOption::VALUE_REQUIRED,
            'Mode in which the output file will be open to write to.',
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

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Service [Aws\CloudWatchLogs\CloudWatchLogsClient] is required for command [Draw\Bundle\AwsToolKitBundle\Command\CloudWatchLogsDownloadCommand] to run.');

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
        $startTime = new DateTimeImmutable('2001-01-01 00:00:00');
        $endTime = new DateTimeImmutable('2001-01-02 00:00:01');
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
            ->expects($this->exactly(2))
            ->method('getLogEvents')
            ->withConsecutive(
                [$logEvents],
                [$logEvents + ['nextToken' => 'next-token']]
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

        $this->assertEquals(
            "Line 1\nLine 2\n",
            file_get_contents($output)
        );
    }

    public function testExecuteAppendFile(): void
    {
        $logGroupName = 'group-name';
        $logStreamName = 'stream-name';
        $startTime = new DateTimeImmutable('2001-01-01 00:00:00');
        $endTime = new DateTimeImmutable('2001-01-02 00:00:01');
        $output = sys_get_temp_dir().'/'.uniqid().'.txt';
        file_put_contents($output, "Before\n");
        register_shutdown_function('unlink', $output);

        $this->cloudWatchLogsClient
            ->expects($this->once())
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

        $this->assertEquals(
            "Before\nLine 1\n",
            file_get_contents($output)
        );
    }
}

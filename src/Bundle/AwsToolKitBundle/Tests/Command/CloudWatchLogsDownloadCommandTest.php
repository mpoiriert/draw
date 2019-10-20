<?php namespace Draw\Bundle\AwsToolKitBundle\Tests\Command;

use Aws\CloudWatchLogs\CloudWatchLogsClient;
use Draw\Bundle\AwsToolKitBundle\Command\CloudWatchLogsDownloadCommand;
use Draw\Component\Tester\Application\CommandDataTester;
use Draw\Component\Tester\Application\CommandTestCase;
use Prophecy\Prophecy\ObjectProphecy;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

class CloudWatchLogsDownloadCommandTest extends CommandTestCase
{
    /**
     * @var ObjectProphecy
     */
    private $cloudWatchLogsClientProphecy;

    /**
     * @var CloudWatchLogsClient
     */
    private $cloudWatchLogsClient;

    public function getCommandName(): string
    {
        return 'draw:aws:cloud-watch-logs:download';
    }

    public function getCommandDescription(): string
    {
        return 'Download logs from cloud watch.';
    }

    public function createCommand(): Command
    {
        $this->cloudWatchLogsClientProphecy = $this->prophesize(CloudWatchLogsClient::class);
        $this->cloudWatchLogsClient = $this->cloudWatchLogsClientProphecy->reveal();
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
            'w+'
        ];
    }

    public function testExecuteNewFile()
    {
        $logGroupName = 'group-name';
        $logStreamName = 'stream-name';
        $output = sys_get_temp_dir() . '/' . uniqid() . '.txt';
        file_put_contents($output, "Before\n");
        register_shutdown_function('unlink', $output);

        $logEvents = [
            "startFromHead" => true,
            "logGroupName" => "group-name",
            "logStreamName" => "stream-name",
            "startTime" => 978307201000,
            "endTime" => 978393601000
        ];

        $this->cloudWatchLogsClientProphecy
            ->__call('getLogEvents', [$logEvents])
            ->shouldBeCalledOnce()
            ->willReturn([
                'events' => [
                    ['message' => 'Line 1']
                ],
                'nextForwardToken' => 'next-token'
            ]);

        $this->cloudWatchLogsClientProphecy
            ->__call('getLogEvents', [$logEvents + ["nextToken" => "next-token"]])
            ->shouldBeCalledOnce()
            ->willReturn([
                'events' => [
                    ['message' => 'Line 2']
                ],
                'nextForwardToken' => 'next-token'
            ]);

        $this->execute(
            compact('logGroupName', 'logStreamName', 'output')
            + ['--startTime' => '2001-01-01 00:00:01', '--endTime' => '2001-01-02 00:00:01']
        )
            ->test(CommandDataTester::create());

        $this->assertEquals(
            "Line 1\nLine 2\n",
            file_get_contents($output)
        );
    }

    public function testExecuteAppendFile()
    {
        $logGroupName = 'group-name';
        $logStreamName = 'stream-name';
        $output = sys_get_temp_dir() . '/' . uniqid() . '.txt';
        file_put_contents($output, "Before\n");
        register_shutdown_function('unlink', $output);

        $this->cloudWatchLogsClientProphecy
            ->__call(
                'getLogEvents',
                [
                    [
                        "startFromHead" => true,
                        "logGroupName" => "group-name",
                        "logStreamName" => "stream-name",
                        "startTime" => 978307201000,
                        "endTime" => 978393601000
                    ]
                ]
            )
            ->shouldBeCalledOnce()
            ->willReturn([
                'events' => [
                    ['message' => 'Line 1']
                ],
                'nextForwardToken' => null
            ]);

        $this->execute(
            compact('logGroupName', 'logStreamName', 'output')
            + ['--startTime' => '2001-01-01 00:00:01', '--endTime' => '2001-01-02 00:00:01', '--fileMode' => 'a+']
        )
            ->test(CommandDataTester::create());

        $this->assertEquals(
            "Before\nLine 1\n",
            file_get_contents($output)
        );
    }
}
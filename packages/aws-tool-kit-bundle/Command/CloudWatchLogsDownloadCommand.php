<?php

namespace Draw\Bundle\AwsToolKitBundle\Command;

use Aws\CloudWatchLogs\CloudWatchLogsClient;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Command to download a cloud watch log locally base on it's log group name, log stream name and a start time/end time.
 */
class CloudWatchLogsDownloadCommand extends Command
{
    /**
     * @var CloudWatchLogsClient
     */
    private $cloudWatchClient;

    public function __construct(CloudWatchLogsClient $cloudWatchClient)
    {
        $this->cloudWatchClient = $cloudWatchClient;
        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setName('draw:aws:cloud-watch-logs:download')
            ->setDescription('Download logs from cloud watch.')
            ->addArgument('logGroupName', InputArgument::REQUIRED, 'The log group name')
            ->addArgument('logStreamName', InputArgument::REQUIRED, 'The log stream name')
            ->addArgument('output', InputArgument::REQUIRED, 'The output file name')
            ->addOption('startTime', null, InputOption::VALUE_REQUIRED, 'Since when the log need to be downloaded.', '- 1 days')
            ->addOption('endTime', null, InputOption::VALUE_REQUIRED, 'End time of the download.', 'now')
            ->addOption('fileMode', null, InputOption::VALUE_REQUIRED, 'Mode in which the output file will be open to write to.', 'w+');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $startTime = strtotime($input->getOption('startTime')) * 1000;
        $endTime = strtotime($input->getOption('endTime')) * 1000;

        $arguments = [
            'startFromHead' => true,
            'logGroupName' => $input->getArgument('logGroupName'),
            'logStreamName' => $input->getArgument('logStreamName'),
            'startTime' => $startTime,
            'endTime' => $endTime,
        ];

        $handle = fopen($input->getArgument('output'), $input->getOption('fileMode'));
        $nextForwardToken = null;
        do {
            $nextToken = $nextForwardToken;
            if ($nextToken) {
                $arguments['nextToken'] = $nextToken;
            }

            $events = $this
                ->cloudWatchClient
                ->getLogEvents($arguments);

            foreach ($events['events'] as $event) {
                fwrite($handle, $event['message'].PHP_EOL);
            }

            $nextForwardToken = $events['nextForwardToken'];
        } while ($nextForwardToken !== $nextToken);

        fclose($handle);

        return 0;
    }
}

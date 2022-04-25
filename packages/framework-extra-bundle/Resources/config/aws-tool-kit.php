<?php

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Draw\Component\AwsToolKit\Command\CloudWatchLogsDownloadCommand;

return static function (ContainerConfigurator $container) {
    $container
        ->services()
            ->defaults()
                ->autoconfigure()
                ->autowire()

            ->set('draw.aws_tool_kit.command.cloud_watch_logs_download', CloudWatchLogsDownloadCommand::class)
            ->alias(CloudWatchLogsDownloadCommand::class, 'draw.aws_tool_kit.command.cloud_watch_logs_download');
};

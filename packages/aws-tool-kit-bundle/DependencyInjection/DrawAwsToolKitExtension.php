<?php

namespace Draw\Bundle\AwsToolKitBundle\DependencyInjection;

use Draw\Bundle\AwsToolKitBundle\Command\CloudWatchLogsDownloadCommand;
use Draw\Bundle\AwsToolKitBundle\Imds\ImdsClientInterface;
use Draw\Bundle\AwsToolKitBundle\Listener\NewestInstanceRoleCheckListener;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

class DrawAwsToolKitExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container): void
    {
        $config = $this->processConfiguration($this->getConfiguration($configs, $container), $configs);

        $container->setDefinition(
            'draw.aws_tool_kit.cloud_watch_logs_download_command',
            new Definition(CloudWatchLogsDownloadCommand::class)
        )
            ->setAutoconfigured(true)
            ->setAutowired(true);

        $container->setAlias(
            CloudWatchLogsDownloadCommand::class,
            'draw.aws_tool_kit.cloud_watch_logs_download_command'
        );

        if ($config['imds_version']) {
            $container
                ->setDefinition(
                    $serviceId = 'draw.aws_tool_kit.imds_client_v'.$config['imds_version'],
                    new Definition(
                        'Draw\Bundle\AwsToolKitBundle\Imds\ImdsClientV'.$config['imds_version']
                    )
                )
                ->setAutoconfigured(true)
                ->setAutowired(true);

            $container->setAlias(
                ImdsClientInterface::class,
                $serviceId
            );
        }

        $this->configureVersioning($config['newest_instance_role_check'], $container);
    }

    private function configureVersioning(
        array $config,
        ContainerBuilder $container
    ): void {
        if (!$this->isConfigEnabled($container, $config)) {
            return;
        }

        $container
            ->setDefinition(
                'draw.aws_tool_kit.newest_instance_role_check_listener',
                new Definition(NewestInstanceRoleCheckListener::class)
            )
            ->setAutowired(true)
            ->setAutoconfigured(true);

        $container->setAlias(
            NewestInstanceRoleCheckListener::class,
            'draw.aws_tool_kit.newest_instance_role_check_listener'
        );
    }
}

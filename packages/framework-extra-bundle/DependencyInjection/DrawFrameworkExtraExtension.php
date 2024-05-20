<?php

namespace Draw\Bundle\FrameworkExtraBundle\DependencyInjection;

use Draw\Bundle\FrameworkExtraBundle\DependencyInjection\Integration\AwsToolKitIntegration;
use Draw\Bundle\FrameworkExtraBundle\DependencyInjection\Integration\ConfigurationIntegration;
use Draw\Bundle\FrameworkExtraBundle\DependencyInjection\Integration\ConsoleIntegration;
use Draw\Bundle\FrameworkExtraBundle\DependencyInjection\Integration\CronIntegration;
use Draw\Bundle\FrameworkExtraBundle\DependencyInjection\Integration\CronJobIntegration;
use Draw\Bundle\FrameworkExtraBundle\DependencyInjection\Integration\DoctrineExtraIntegration;
use Draw\Bundle\FrameworkExtraBundle\DependencyInjection\Integration\EntityMigratorIntegration;
use Draw\Bundle\FrameworkExtraBundle\DependencyInjection\Integration\FeatureIntegration;
use Draw\Bundle\FrameworkExtraBundle\DependencyInjection\Integration\LoggerIntegration;
use Draw\Bundle\FrameworkExtraBundle\DependencyInjection\Integration\LogIntegration;
use Draw\Bundle\FrameworkExtraBundle\DependencyInjection\Integration\MailerIntegration;
use Draw\Bundle\FrameworkExtraBundle\DependencyInjection\Integration\MessengerIntegration;
use Draw\Bundle\FrameworkExtraBundle\DependencyInjection\Integration\OpenApiIntegration;
use Draw\Bundle\FrameworkExtraBundle\DependencyInjection\Integration\ProcessIntegration;
use Draw\Bundle\FrameworkExtraBundle\DependencyInjection\Integration\SecurityIntegration;
use Draw\Bundle\FrameworkExtraBundle\DependencyInjection\Integration\SystemMonitoringIntegration;
use Draw\Bundle\FrameworkExtraBundle\DependencyInjection\Integration\TesterIntegration;
use Draw\Bundle\FrameworkExtraBundle\DependencyInjection\Integration\ValidatorIntegration;
use Draw\Bundle\FrameworkExtraBundle\DependencyInjection\Integration\VersioningIntegration;
use Draw\Bundle\FrameworkExtraBundle\DependencyInjection\Integration\WorkflowIntegration;
use Draw\Component\DependencyInjection\Integration\ExtendableExtensionTrait;
use Draw\Component\DependencyInjection\Integration\IntegrationInterface;
use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;

class DrawFrameworkExtraExtension extends Extension implements PrependExtensionInterface
{
    use ExtendableExtensionTrait;

    /**
     * @param array<IntegrationInterface>|null $integrations
     */
    public function __construct(?array $integrations = null)
    {
        if (null === $integrations) {
            $this->registerDefaultIntegrations();
        } else {
            $this->integrations = $integrations;
        }
    }

    private function provideExtensionClasses(): array
    {
        return [
            AwsToolKitIntegration::class,
            ConfigurationIntegration::class,
            ConsoleIntegration::class,
            CronIntegration::class,
            CronJobIntegration::class,
            DoctrineExtraIntegration::class,
            EntityMigratorIntegration::class,
            FeatureIntegration::class,
            LoggerIntegration::class,
            LogIntegration::class,
            MailerIntegration::class,
            MessengerIntegration::class,
            OpenApiIntegration::class,
            ProcessIntegration::class,
            SecurityIntegration::class,
            SystemMonitoringIntegration::class,
            TesterIntegration::class,
            ValidatorIntegration::class,
            VersioningIntegration::class,
            WorkflowIntegration::class,
        ];
    }

    public function getConfiguration(array $config, ContainerBuilder $container): ConfigurationInterface
    {
        return new Configuration($this->integrations);
    }

    public function load(array $configs, ContainerBuilder $container): void
    {
        $config = $this->loadIntegrations($configs, $container);

        $container->setParameter('draw.symfony_console_path', $config['symfony_console_path']);
    }

    public function prepend(ContainerBuilder $container): void
    {
        $this->prependIntegrations($container, 'draw_framework_extra');
    }
}

<?php

namespace Draw\Bundle\FrameworkExtraBundle\DependencyInjection;

use Draw\Bundle\FrameworkExtraBundle\DependencyInjection\Integration\LoggerIntegration;
use Draw\Bundle\FrameworkExtraBundle\DependencyInjection\Integration\LogIntegration;
use Draw\Bundle\FrameworkExtraBundle\DependencyInjection\Integration\WorkflowIntegration;
use Draw\Component\Application\DependencyInjection\ConfigurationIntegration;
use Draw\Component\Application\DependencyInjection\CronIntegration;
use Draw\Component\Application\DependencyInjection\FeatureIntegration;
use Draw\Component\Application\DependencyInjection\SystemMonitoringIntegration;
use Draw\Component\Application\DependencyInjection\VersioningIntegration;
use Draw\Component\AwsToolKit\DependencyInjection\AwsToolKitIntegration;
use Draw\Component\Console\DependencyInjection\ConsoleIntegration;
use Draw\Component\CronJob\DependencyInjection\CronJobIntegration;
use Draw\Component\DependencyInjection\Integration\ExtendableExtensionTrait;
use Draw\Component\DependencyInjection\Integration\IntegrationInterface;
use Draw\Component\EntityMigrator\DependencyInjection\EntityMigratorIntegration;
use Draw\Component\Mailer\DependencyInjection\MailerIntegration;
use Draw\Component\Messenger\DependencyInjection\MessengerIntegration;
use Draw\Component\OpenApi\DependencyInjection\OpenApiIntegration;
use Draw\Component\Process\DependencyInjection\ProcessIntegration;
use Draw\Component\Security\DependencyInjection\SecurityIntegration;
use Draw\Component\Tester\DependencyInjection\TesterIntegration;
use Draw\Component\Validator\DependencyInjection\ValidatorIntegration;
use Draw\DoctrineExtra\DependencyInjection\DoctrineExtraIntegration;
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

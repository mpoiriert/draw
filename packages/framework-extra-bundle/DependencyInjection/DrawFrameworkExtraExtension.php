<?php

namespace Draw\Bundle\FrameworkExtraBundle\DependencyInjection;

use Draw\Bundle\FrameworkExtraBundle\DependencyInjection\Integration\AwsToolKitIntegration;
use Draw\Bundle\FrameworkExtraBundle\DependencyInjection\Integration\ConfigurationIntegration;
use Draw\Bundle\FrameworkExtraBundle\DependencyInjection\Integration\ConsoleIntegration;
use Draw\Bundle\FrameworkExtraBundle\DependencyInjection\Integration\CronIntegration;
use Draw\Bundle\FrameworkExtraBundle\DependencyInjection\Integration\DoctrineExtraIntegration;
use Draw\Bundle\FrameworkExtraBundle\DependencyInjection\Integration\IntegrationInterface;
use Draw\Bundle\FrameworkExtraBundle\DependencyInjection\Integration\LoggerIntegration;
use Draw\Bundle\FrameworkExtraBundle\DependencyInjection\Integration\LogIntegration;
use Draw\Bundle\FrameworkExtraBundle\DependencyInjection\Integration\MailerIntegration;
use Draw\Bundle\FrameworkExtraBundle\DependencyInjection\Integration\MessengerIntegration;
use Draw\Bundle\FrameworkExtraBundle\DependencyInjection\Integration\OpenApiIntegration;
use Draw\Bundle\FrameworkExtraBundle\DependencyInjection\Integration\PrependIntegrationInterface;
use Draw\Bundle\FrameworkExtraBundle\DependencyInjection\Integration\ProcessIntegration;
use Draw\Bundle\FrameworkExtraBundle\DependencyInjection\Integration\SecurityIntegration;
use Draw\Bundle\FrameworkExtraBundle\DependencyInjection\Integration\SystemMonitoringIntegration;
use Draw\Bundle\FrameworkExtraBundle\DependencyInjection\Integration\TesterIntegration;
use Draw\Bundle\FrameworkExtraBundle\DependencyInjection\Integration\ValidatorIntegration;
use Draw\Bundle\FrameworkExtraBundle\DependencyInjection\Integration\VersioningIntegration;
use Draw\Bundle\FrameworkExtraBundle\DependencyInjection\Integration\WorkflowIntegration;
use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
use Symfony\Component\DependencyInjection\Loader\PhpFileLoader;

class DrawFrameworkExtraExtension extends Extension implements PrependExtensionInterface
{
    /**
     * @var array|IntegrationInterface[]
     */
    private array $integrations = [];

    public function __construct(?array $integrations = null)
    {
        if (null === $integrations) {
            $this->registerDefaultIntegrations();
        } else {
            $this->integrations = $integrations;
        }
    }

    private function registerDefaultIntegrations(): void
    {
        $this->integrations[] = new AwsToolKitIntegration();
        $this->integrations[] = new ConfigurationIntegration();
        $this->integrations[] = new ConsoleIntegration();
        $this->integrations[] = new CronIntegration();
        $this->integrations[] = new DoctrineExtraIntegration();
        $this->integrations[] = new LoggerIntegration();
        $this->integrations[] = new LogIntegration();
        $this->integrations[] = new OpenApiIntegration();
        $this->integrations[] = new MailerIntegration();
        $this->integrations[] = new MessengerIntegration();
        $this->integrations[] = new ProcessIntegration();
        $this->integrations[] = new SecurityIntegration();
        $this->integrations[] = new SystemMonitoringIntegration();
        $this->integrations[] = new TesterIntegration();
        $this->integrations[] = new ValidatorIntegration();
        $this->integrations[] = new VersioningIntegration();
        $this->integrations[] = new WorkflowIntegration();
    }

    public function getConfiguration(array $config, ContainerBuilder $container): ConfigurationInterface
    {
        return new Configuration($this->integrations);
    }

    public function load(array $configs, ContainerBuilder $container): void
    {
        foreach ($this->integrations as $integration) {
            $container->addObjectResource($integration);
        }

        $config = $this->processConfiguration($this->getConfiguration($configs, $container), $configs);
        $loader = new PhpFileLoader($container, new FileLocator(\dirname(__DIR__).'/Resources/config'));

        $container->setParameter('draw.symfony_console_path', $config['symfony_console_path']);

        foreach ($this->integrations as $integration) {
            if ($this->isConfigEnabled($container, $config[$integration->getConfigSectionName()])) {
                $integration->load($config[$integration->getConfigSectionName()], $loader, $container);
            }
        }
    }

    public function prepend(ContainerBuilder $container): void
    {
        $configs = $container->getExtensionConfig('draw_framework_extra');

        $config = $this->processConfiguration(
            $this->getConfiguration($configs, $container),
            $container->getParameterBag()->resolveValue($configs)
        );

        foreach ($this->integrations as $integration) {
            if (!$integration instanceof PrependIntegrationInterface) {
                continue;
            }

            $integrationConfiguration = $config[$integration->getConfigSectionName()];

            if ($this->isConfigEnabled($container, $integrationConfiguration)) {
                $integration->prepend($container, $integrationConfiguration);
            }
        }
    }
}

<?php namespace Draw\Bundle\CronBundle\DependencyInjection;

use Draw\Bundle\CronBundle\CronManager;
use Draw\Bundle\CronBundle\Model\Job;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\HttpKernel\DependencyInjection\ConfigurableExtension;
use Symfony\Component\DependencyInjection\Loader;

class DrawCronExtension extends ConfigurableExtension
{
    protected function loadInternal(array $config, ContainerBuilder $container)
    {
        $loader = new Loader\XmlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        $loader->load('services.xml');

        $cronManagerDefinition = $container->getDefinition(CronManager::class);
        foreach ($config['jobs'] as $jobData) {
            $jobDefinition = new Definition(Job::class);
            $jobDefinition->setArguments([
                $jobData['name'],
                $jobData['command'],
                $jobData['expression'],
                $jobData['enabled'],
                $jobData['description']
            ]);

            $jobDefinition->addMethodCall('setOutput', [$jobData['output']]);

            $cronManagerDefinition->addMethodCall('addJob', [$jobDefinition]);
        }
    }
}
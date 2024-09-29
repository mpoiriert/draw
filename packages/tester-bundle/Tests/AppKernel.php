<?php

namespace Draw\Bundle\TesterBundle\Tests;

use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Kernel;

class AppKernel extends Kernel
{
    public function registerBundles(): iterable
    {
        return [
            new \Symfony\Bundle\FrameworkBundle\FrameworkBundle(),
            new \Draw\Bundle\TesterBundle\DrawTesterBundle(),
        ];
    }

    protected function build(ContainerBuilder $container): void
    {
        $container->prependExtensionConfig(
            'framework',
            [
                'test' => true,
                'messenger' => [
                    'transports' => [
                        'memory' => 'in-memory://',
                    ],
                ],
            ]
        );

        $container->prependExtensionConfig(
            'draw_tester',
            [
                'profiling' => false,
            ]
        );
    }

    public function registerContainerConfiguration(LoaderInterface $loader): void
    {
    }

    public function getCacheDir(): string
    {
        return sys_get_temp_dir().'/tester_bundle/var/cache/'.$this->environment;
    }

    public function getLogDir(): string
    {
        return sys_get_temp_dir().'/tester_bundle/var/log';
    }
}

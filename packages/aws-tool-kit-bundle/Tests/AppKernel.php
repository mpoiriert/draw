<?php

namespace Draw\Bundle\AwsToolKitBundle\Tests;

use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\HttpKernel\Kernel;

class AppKernel extends Kernel
{
    public function registerBundles()
    {
        return [
            new \Symfony\Bundle\FrameworkBundle\FrameworkBundle(),
            new \Draw\Bundle\AwsToolKitBundle\DrawAwsToolKitBundle(),
            new \Aws\Symfony\AwsBundle(),
        ];
    }

    public function registerContainerConfiguration(LoaderInterface $loader): void
    {
        $loader->load(__DIR__.'/config.php');
    }

    public function getCacheDir(): string
    {
        return sys_get_temp_dir().'/aws_tool_kit_bundle/var/cache/'.$this->environment;
    }

    public function getLogDir(): string
    {
        return sys_get_temp_dir().'/aws_tool_kit_bundle/var/log';
    }
}

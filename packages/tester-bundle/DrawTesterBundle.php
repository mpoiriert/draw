<?php

namespace Draw\Bundle\TesterBundle;

use Draw\Bundle\TesterBundle\DependencyInjection\Compiler\MessengerPass;
use Draw\Bundle\TesterBundle\DependencyInjection\Compiler\PublicPass;
use Draw\Bundle\TesterBundle\DependencyInjection\CompilerPass;
use Draw\Component\Profiling\ProfilerCoordinator;
use Symfony\Component\DependencyInjection\Compiler\PassConfig;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class DrawTesterBundle extends Bundle
{
    public static $ids = [];

    public static function addServicesToTest($ids)
    {
        $ids = (array) $ids;
        self::$ids += $ids;
    }

    public function build(ContainerBuilder $container)
    {
        $container->addCompilerPass(new CompilerPass());
        $container->addCompilerPass(new MessengerPass(), PassConfig::TYPE_OPTIMIZE);
        $container->addCompilerPass(new PublicPass(), PassConfig::TYPE_AFTER_REMOVING);
    }

    public function boot()
    {
        if ($this->container->has(ProfilerCoordinator::class)) {
            $this->container->get(ProfilerCoordinator::class)->startAll();
        }
    }
}

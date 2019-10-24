<?php namespace Draw\Bundle\TesterBundle;

use Draw\Bundle\TesterBundle\Config\ServiceIdsResourceCheck;
use Draw\Component\Profiling\ProfilerCoordinator;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\Compiler\PassConfig;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class DrawTesterBundle extends Bundle implements CompilerPassInterface
{
    public static $ids = [];

    public static function addServicesToTest($ids)
    {
        $ids = (array)$ids;
        self::$ids += $ids;
    }

    public function build(ContainerBuilder $container)
    {
        $container->addCompilerPass($this, PassConfig::TYPE_BEFORE_REMOVING);
    }

    public function process(ContainerBuilder $container)
    {
        $file = $container->getParameter('kernel.cache_dir') . '/serviceIdsToTest.json';
        $container->addResource(new ServiceIdsResourceCheck($file));
        self::$ids = array_unique(self::$ids);
        foreach (self::$ids as $id) {
            if ($container->hasDefinition($id)) {
                $container->getDefinition($id)->setPublic(true);
            }

            if ($container->hasAlias($id)) {
                $container->getAlias($id)->setPublic(true);
            }
        }
    }

    public function boot()
    {
        if ($this->container->has(ProfilerCoordinator::class)) {
            $this->container->get(ProfilerCoordinator::class)->startAll();
        }
    }
}
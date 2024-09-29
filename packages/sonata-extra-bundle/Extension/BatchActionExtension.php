<?php

namespace Draw\Bundle\SonataExtraBundle\Extension;

use Doctrine\Inflector\InflectorFactory;
use Draw\Bundle\SonataExtraBundle\Controller\BatchAdminController;
use Sonata\AdminBundle\Admin\AbstractAdminExtension;
use Sonata\AdminBundle\Admin\AdminInterface;
use Sonata\AdminBundle\Route\RouteCollectionInterface;

class BatchActionExtension extends AbstractAdminExtension
{
    public function configureRoutes(AdminInterface $admin, RouteCollectionInterface $collection): void
    {
        if (!$collection->has('batch')) {
            return;
        }

        $collection
            ->get('batch')
            ->setDefault('_controller', BatchAdminController::class.'::batchAction')
        ;
    }

    public function configureBatchActions(AdminInterface $admin, array $actions): array
    {
        foreach ($actions as $name => $configuration) {
            if (isset($configuration['controller'])) {
                continue;
            }

            $actions[$name]['controller'] = \sprintf(
                '%s::batchAction%s',
                $admin->getBaseControllerName(),
                InflectorFactory::create()->build()->classify($name)
            );
        }

        return $actions;
    }
}

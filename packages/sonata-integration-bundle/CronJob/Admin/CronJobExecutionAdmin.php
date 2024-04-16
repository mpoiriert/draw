<?php

declare(strict_types=1);

namespace Draw\Bundle\SonataIntegrationBundle\CronJob\Admin;

use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Route\RouteCollectionInterface;
use Sonata\AdminBundle\Show\ShowMapper;

class CronJobExecutionAdmin extends AbstractAdmin
{
    public function configureGridFields(array $fields): array
    {
        return array_merge(
            $fields,
            [
                'requestedAt' => [],
                'force' => [],
                'executionStartedAt' => [],
                'executionEndedAt' => [],
                'exitCode' => [],
                'actions' => [
                    'type' => ListMapper::TYPE_ACTIONS,
                    'options' => [
                        'virtual_field' => true,
                        'admin' => $this,
                        'actions' => [
                            'show' => [
                                'label' => 'Show',
                                'icon' => 'fa-eye',
                                'route_object' => 'show',
                                'check_callback' => fn (object $object) => $this->hasAccess('show', $object),
                            ],
                        ],
                    ],
                ],
            ]
        );
    }

    protected function configureShowFields(ShowMapper $show): void
    {
        $show
            ->add('requestedAt')
            ->add('force')
            ->add('executionStartedAt')
            ->add('executionEndedAt')
            ->add('executionDelay')
            ->add('exitCode')
            ->add('error', 'json');
    }

    protected function configureRoutes(RouteCollectionInterface $collection): void
    {
        $collection->clearExcept('show');
    }
}

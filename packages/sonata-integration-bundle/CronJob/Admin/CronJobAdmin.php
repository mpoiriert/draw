<?php

declare(strict_types=1);

namespace Draw\Bundle\SonataIntegrationBundle\CronJob\Admin;

use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Show\ShowMapper;

class CronJobAdmin extends AbstractAdmin
{
    protected function configureDatagridFilters(DatagridMapper $filter): void
    {
        $filter
            ->add('id')
            ->add('name')
            ->add('command')
            ->add('active');
    }

    protected function configureListFields(ListMapper $list): void
    {
        $list
            ->addIdentifier('name')
            ->add('command')
            ->add('schedule')
            ->add('active', null, ['editable' => true])
            ->add('timeToLive')
            ->add('priority');
    }

    protected function configureFormFields(FormMapper $form): void
    {
        $form
            ->with('Definition', ['class' => 'col-md-8'])
                ->add('name')
                ->add(
                    'command',
                    null,
                    [
                        'help' => 'Enter the full command to run excluding stdOut and stdErr directive (... 2>&1 | logger -t ...)<p>Parameters bag is available. Use like %kernel.project_dir%</p>',
                    ]
                )
                ->add('schedule')
                ->add('active')
            ->end()
            ->with('Queue Configuration', ['class' => 'col-md-4'])
                ->add('timeToLive')
                ->add('priority')
            ->end();
    }

    protected function configureShowFields(ShowMapper $show): void
    {
        $show
            ->tab('Cron Job')
                ->with('General')
                    ->add('name')
                    ->add('command')
                    ->add('schedule')
                    ->add('active', null, ['editable' => true])
                    ->add('timeToLive')
                    ->add('priority')
                ->end()
                ->with('Executions')
                ->end()
            ->end();
    }
}
